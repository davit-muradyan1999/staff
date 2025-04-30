<?php

namespace App\Http\Controllers\Company;

use DB;
use Log;
use Misc;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use App\Models\Job,
    App\Models\User,
    App\Models\ShiftReason,
    App\Models\JobApplication,
    App\Models\Position,
    App\Models\EmployeeBalance,
    App\Models\EmployeeBalanceList,
    App\Models\CompanyBalance,
    App\Models\CompanyBalanceList,
    App\Models\Establishment,
    App\Models\Branch;

class FinanceController extends Controller
{
    public function balanceInfo(Request $request) {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');
        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        if(Auth::user()->role == 'ADMIN') {
            $companyId = $request->companyId;
        } else {
            $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;
        }

        $selectedBranches = $request->branches;
        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $selectedBranches = Misc::getModeratorBranches();

            $moderatorRoleBalance = $this->tempBalanceList($startedAt, $finishedAt, $companyId, $request, $selectedBranches);
        }

        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $brancheIds = Misc::getModeratorBranches();
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->whereIn('id', $brancheIds)->get();
        } else {
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $companyId)->get();
        }

        $companyBalance = CompanyBalance::where('company_id', $companyId)->first();
        $companyBalancelists = $this->tempBalanceList($startedAt, $finishedAt, $companyId, $request, $selectedBranches);
        $company = User::select('company_name')->where('id', $companyId)->first();

        $template = Auth::user()->role == 'ADMIN' ? 'Company/AdminBalance' : 'Company/Balance';

        return Inertia::render($template, [
            'title'                   => __('general.balance'),
            'companyBalance'          => $companyBalance,
            'companyBalancelists'     => $companyBalancelists->get(),
            'moderatorBalancelistSum' => isset($moderatorRoleBalance) ? $moderatorRoleBalance->sum('amount') : 0,
            'startedAt'               => $startedAt,
            'finishedAt'              => $finishedAt,
            'branches'                => $branches,
            'company'                 => $company,
            'establishments'          => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->whereIn('status', ['CONFIRMED', 'PASSIVATED'])->where('company_id', $companyId)->get(),
        ]);
    }

    public function tempBalanceList($startedAt, $finishedAt, $companyId, $request, $selectedBranches) {
        return CompanyBalanceList::where('company_id', $companyId)->with([
            'jobCheckList' => function($q) {
                $q->select('id', 'user_id', 'job_time_graphic_id', 'started_at', 'finished_at', 'created_at')->with([
                    'jobTimeGraphic'
                ]);
            },
            'company' => function($q) {
                $q->select('id', 'company_name');
            },
            'employee' => function($q) {
                $q->select('id', 'name');
            },
            'job' => function($q) {
                $q->select('id', 'job_type_id', 'establishment_id', 'branche_id')->with([
                    'establishment' => function($query) {
                        $query->select('id', 'user_id', 'company_id', 'name->ru as name_e', 'obligation->ru as obligation_e', 'requirement->ru as requirement_e', 'salary', 'tax', 'commission', 'gender', 'img', 'absence_disability')->with([
                            'company' => function($query) {
                                $query->select('id', 'email', 'name', 'company_name', 'company_phone')->with([
                                    'companyInfo' => function($query) {
                                        $query->select('id', 'user_id', 'email', 'phone');
                                    }
                                ]);
                            }
                        ]);
                    },
                    'branche' => function($query) {
                        $query->select('id', 'title->ru as title_b', 'phone->ru as phone_b', 'address->ru as address_b');
                    },
                ]);
            },
        ])->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                    $q->whereHas('job', function (Builder $q) use ($request) {
                        $q->whereHas('establishment', function (Builder $query) use ($request) {
                            $query->where('name', 'like', '%' . $request->text . '%');
                        })->orWhereHas('branche', function (Builder $query) use ($request) {
                            $query->where('title', 'like', '%' . $request->text . '%');
                        })->orWhere('id', 'like', '%' . $request->text . '%');
                    })->orWhere(function ($q) use ($request) {
                        $q->whereHas('company', function (Builder $query) use ($request) {
                            $query->where('name', 'like', '%' . $request->text . '%')->orWhere('company_name', 'like', '%' . $request->text . '%');
                        });
                    })->orWhere(function ($q) use ($request) {
                        $q->whereHas('employee', function (Builder $query) use ($request) {
                            $query->where('name', 'like', '%' . $request->text . '%');
                        });
                    });
                });
        })->when($startedAt, function ($q) use ($startedAt) {
            return $q->where(function ($q) use ($startedAt) {
                $q->whereDate('created_at', '>=', $startedAt);
            });
        })->when($finishedAt, function ($q) use ($finishedAt) {
            return $q->where(function ($q) use ($finishedAt) {
                $q->whereDate('created_at', '<=', $finishedAt);
            });
        })->when($request->establishments, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('job', function (Builder $q) use ($request) {
                    $q->whereIn('establishment_id', $request->establishments);
                });
            });
        })->when($selectedBranches, function ($q) use ($selectedBranches) {
            return $q->where(function ($q) use ($selectedBranches) {
                $q->whereHas('job', function (Builder $q) use ($selectedBranches) {
                    $q->whereIn('branche_id', $selectedBranches);
                });
            });
        })->when($request->type, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        })->orderBy('created_at', 'DESC');
    }
}
