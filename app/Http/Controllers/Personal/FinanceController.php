<?php

namespace App\Http\Controllers\Personal;

use DB;
use Log;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Job,
    App\Models\User,
    App\Models\ShiftReason,
    App\Models\JobApplication,
    App\Models\Position,
    App\Models\EmployeeBalance,
    App\Models\EmployeeBalanceList,
    App\Models\Transfer,
    App\Models\TransferDetail,
    App\Models\JobCheckList;

class FinanceController extends Controller
{
    public function paymentInfo(Request $request) {
        $startedAt = $request->startedAt ? date("Y-m-d", strtotime($request->startedAt)) . ' 00:00:00' : '';
        $finishedAt = $request->finishedAt ? date("Y-m-d", strtotime($request->finishedAt)) . ' 23:59:59' : '';
        $bonus = $request->bonus == '1' ? 1 : ($request->bonus == '0' ? 0 : 1);

        $transfers = Transfer::with([
            'payingUser' => function($q) {
                $q->select('id', 'name', 'company_name');
            },
            'paidEmployee' => function($q) {
                $q->select('id', 'name');
            },
            'details' => function($q) {
                $q->with([
                    'employeeBalanceList' => function($q) {
                        $q->with([
                            'jobCheckList' => function($q) {
                                $q->with([
                                    'company' => function ($q) {
                                        $q->select('id', 'company_name');
                                    },
                                    'branche' => function ($q) {
                                        $q->select('id', 'title->ru as title_b');
                                    },
                                    'job' => function ($q) {
                                        $q->select('id', 'establishment_id')->with([
                                            'establishment' => function($q) {
                                                $q->select('id', 'name->ru as name_e');
                                            }
                                        ]);
                                    },
                                    'jobTimeGraphic'
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ])
        ->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('payingUser', function (Builder $query) use ($request) {
                    $query->where('name', 'like', '%' . $request->text . '%');
                })->orWhereHas('paidEmployee', function (Builder $query) use ($request) {
                    $query->where('name', 'like', '%' . $request->text . '%');
                });
            });
        })
        ->when($startedAt, function ($q) use ($startedAt) {
            return $q->where(function ($q) use ($startedAt) {
                $q->whereDate('created_at', '>=', $startedAt);
            });
        })->when($finishedAt, function ($q) use ($finishedAt) {
            return $q->where(function ($q) use ($finishedAt) {
                $q->whereDate('created_at', '<=', $finishedAt);
            });
        })
        ->whereHas('details', function (Builder $query) use ($request) {
            $query->where('paid_employee_id', '=', Auth::user()->id);
        })
        ->orderBy('id', 'DESC')
        ->paginate(50)->appends(request()->query());


        $totalSum = Transfer::where('paid_employee_id', Auth::user()->id)->sum('amount');

        $totalTime = JobCheckList::select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) - (CASE WHEN job_check_lists.is_paid_lunch = 0 THEN job_check_lists.lunch ELSE 0 END)) as time"))
            ->where('job_check_lists.employee_user_id', Auth::user()->id)
            ->join('employee_balance_lists', function($join) {
                $join->on('employee_balance_lists.employee_id', '=', 'job_check_lists.employee_user_id');
                $join->on('employee_balance_lists.job_check_list_id', '=', 'job_check_lists.id');
                $join->where('employee_balance_lists.is_paid', 1);
                $join->whereNull('employee_balance_lists.deleted_at');
            })
            ->groupBy('job_check_lists.employee_user_id')
            ->get();


        // EmployeeBalanceList::where('employee_id', Auth::user()->id)->where('is_paid', 1)->withCount([
        //     'jobCheckList AS total_minutes' => function ($query) {
        //         $query->select(DB::raw("SUM((TIMESTAMPDIFF(MINUTE, started_at, finished_at) / 60) - lunch / 60)"));
        //     },
        // ])->groupBy('employee_id')->first();



        // dd($totalTime);

        return Inertia::render('Personal/PaymentInfo', [
            'title'     =>  __('general.finance'),
            'transfers' =>  $transfers,
            'totalSum'  =>  $totalSum,
            'totalTime' =>  isset($totalTime[0]->time) ? $totalTime[0]->time : 0,
        ]);
    }

    public function balance(Request $request) {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');
        $employeeId = Auth::user()->role == 'WORKER' ? Auth::user()->id : $request->employeeId;
        $isAdmin = Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR' ? true : false;
        $bonus = $request->bonus == '1' ? 1 : ($request->bonus == '0' ? 0 : 1);

        $employeeBalance = EmployeeBalance::where('employee_user_id', $employeeId)->first();
        $employeeBalancelists = EmployeeBalanceList::where('employee_id', $employeeId)->with([
            'jobCheckList' => function($q) {
                $q->select('id', 'user_id', 'job_time_graphic_id', 'started_at', 'finished_at', 'created_at')->with([
                    'jobTimeGraphic'
                ]);
            },
            'company' => function($q) {
                $q->select('id', 'company_name');
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
                        })->orWhere('id', 'like', '%' . $request->text . '%');;
                    })->orWhere(function ($q) use ($request) {
                        $q->whereHas('company', function (Builder $query) use ($request) {
                            $query->where('name', 'like', '%' . $request->text . '%')->orWhere('company_name', 'like', '%' . $request->text . '%');
                        });
                    });
                });
        })->when($request->positions, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('job', function (Builder $q) use ($request) {
                    $q->whereHas('establishment', function (Builder $q) use ($request) {
                        $q->whereHas('positions', function (Builder $query) use ($request) {
                            $query->whereIn('position_id', $request->positions);
                        });
                    });
                });
            });
        })->when($request->companies, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('job', function (Builder $q) use ($request) {
                    $q->whereIn('company_id', $request->companies);;
                });
            });
        })->when($request->type, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        })->when($isAdmin, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('is_paid', $request->isPaid);
            });
        });

        if($isAdmin) {
            $employeeBalancelists = $employeeBalancelists->where(function ($q) use($startedAt, $finishedAt, $bonus) {
                $q->whereHas('jobCheckList', function (Builder $query) use($startedAt, $finishedAt, $bonus) {
                    if($startedAt) {
                        $query->where('started_at', '>=', $startedAt . ' 00:00:00');
                    }
                    if($finishedAt) {
                        $query->where('finished_at', '<=', $finishedAt . ' 23:59:59');
                    }
                });

                if($bonus) {
                    $q->orDoesntHave('jobCheckList');
                }
            })
            ->orderBy('type', 'DESC');

        } else {
            $employeeBalancelists = $employeeBalancelists->when($startedAt, function ($q) use ($startedAt) {
                return $q->where(function ($q) use ($startedAt) {
                    $q->whereDate('created_at', '>=', $startedAt);
                });
            })->when($finishedAt, function ($q) use ($finishedAt) {
                return $q->where(function ($q) use ($finishedAt) {
                    $q->whereDate('created_at', '<=', $finishedAt);
                });
            });
        }


        $employeeBalancelistsHours = clone $employeeBalancelists;
        $employeeBalancelistsAmount = clone $employeeBalancelists;

        $totalAmount = 0;

        if(Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') {
            $totalAmount = $employeeBalancelistsHours->sum('amount');
        }

        $estPosIDS = JobApplication::select('establishment_positions.position_id as est_position_id')->where('job_applications.user_id', $employeeId)
                        ->join('jobs', function($join) {
                            $join->on('jobs.id', '=', 'job_applications.job_id')
                                ->whereNull('jobs.deleted_at');
                        })->join('establishments', function($join) {
                            $join->on('establishments.id', '=', 'jobs.establishment_id')
                                ->whereNull('establishments.deleted_at');
                        })->join('establishment_positions', function($join) {
                            $join->on('establishment_positions.establishment_id', '=', 'establishments.id')
                                ->whereNull('establishment_positions.deleted_at');
                        })->groupBy('est_position_id')->pluck('est_position_id')->toArray();

        $companies = JobApplication::select('users.id as ju_user_id', 'users.company_name as ju_user_company_name')->where('job_applications.user_id', $employeeId)
                        ->join('jobs', function($join) {
                            $join->on('jobs.id', '=', 'job_applications.id')
                                ->whereNull('jobs.deleted_at');
                        })->join('users', function($join) {
                            $join->on('users.id', '=', 'jobs.user_id')
                                ->whereNull('users.deleted_at');
                        })->groupBy('users.id')->get();

        $template = Auth::user()->role == 'WORKER' ? 'Balance' : 'AdminBalance';
        $employee = Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR' ? User::select('id', 'name')->where('id', $employeeId)->first() : [];

        return Inertia::render('Personal/' . $template, [
            'title'                => __('general.balance'),
            'employeeBalance'      => $employeeBalance,
            'employeeBalancelists' => $employeeBalancelists->get(),
            // 'totalJobs'          => $totalJobs,
            // 'acceptedJobs'       => $acceptedJobs,
            'startedAt'          => $startedAt,
            'finishedAt'         => $finishedAt,
            // 'filterShiftReasons' => $filterShiftReasons,
            'companies'          => $companies,
            'positions'          => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->whereIn('id', $estPosIDS)->get(),
            'shiftReasons'       => ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'worker'             => Auth::user()->role == 'WORKER' ? true : false,
            'employee'           => $employee,
            'totalAmount'        => $totalAmount
        ]);
    }
}
