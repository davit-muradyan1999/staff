<?php

namespace App\Http\Controllers\Company;

use DB;
use Log;
use Auth;
use Misc;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Company\AddBlackListRequest;
use Inertia\Inertia;
use App\Models\Branch,
    App\Models\User,
    App\Models\Job,
    App\Models\BlackList;

class EmployeeController extends Controller
{
    public function tempTmployees($request) {
        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        $selectedBranches = $request->branches;
        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $selectedBranches = Misc::getModeratorBranches();
        }

        return Job::select('jobs.*', 'job_applications.user_id as jb_user_id', 'job_applications.created_at as jb_created_at', 'users.name as user_name', 'users.profile_photo_path as user_profile_photo_path', 'LatestEmployee.status as bl_status', 'black_lists.id as general_bl_id')->with([
            'establishment' => function($query) {
                $query->select('id', 'user_id', 'company_id', 'name->ru as name_e', 'obligation->ru as obligation_e', 'requirement->ru as requirement_e', 'salary', 'tax', 'commission', 'gender', 'img', 'absence_disability')->with([
                    'company' => function($query) {
                        $query->select('id', 'email', 'name', 'company_name', 'company_phone')->with([
                            'companyInfo' => function($query) {
                                $query->select('id', 'user_id', 'email', 'phone');
                            }
                        ]);
                    },
                    'driverLicenseLists' => function($q) {
                        $q->with([
                            'driverLicense' => function($q) {
                                $q->select('id',  'name->'. app()->getLocale(). ' as name_d');
                            }
                        ]);
                    },
                    'meanOfTransportLists' => function($q) {
                        $q->with([
                            'meanOfTransport' => function($q) {
                                $q->select('id',  'name->'. app()->getLocale(). ' as name_e');
                            }
                        ]);
                    },
                ]);
            },
            'branche' => function($query) {
                $query->select('id', 'title->ru as title_b', 'phone->ru as phone_b', 'address->ru as address_b');
            },
            'currency' => function($query) {
                $query->select('id', 'name');
            },
            'graphics',

        ])
        ->where('jobs.user_id', $companyId)
        ->withCount([
            'graphics AS total_hours' => function ($query) {
                $query->select(DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, started_at, finished_at) / 60), 2) - lunch / 60)"));
            },
            'graphics AS total_minutes' => function ($query) {
                $query->select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, started_at, finished_at) - lunch)"));
            },
            'graphics AS total_days',
            'applications AS total_applications',
        ])
        ->when($selectedBranches, function ($q) use ($selectedBranches) {
            return $q->where(function ($q) use ($selectedBranches) {
                $q->whereHas('branche', function (Builder $query) use ($selectedBranches) {
                    $query->whereIn('branches.id', $selectedBranches);
                });
            });
        })
        ->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('applications', function (Builder $q) use ($request) {
                    $q->whereHas('user', function (Builder $query) use ($request) {
                        $query->where('name', 'like', '%' . $request->text . '%');
                    });
                })->orWhere(function ($q) use ($request) {
                    $q->whereHas('branche', function (Builder $query) use ($request) {
                        $query->where('title', 'like', '%' . $request->text . '%');
                    });
                })->orWhere(function ($q) use ($request) {
                    $q->whereHas('establishment', function (Builder $q) use ($request) {
                        $q->whereHas('positions', function (Builder $query) use ($request) {
                            $query->where('name', 'like', '%' . $request->text . '%');
                        });
                    });
                })->orWhere(function ($q) use ($request) {
                    $q->where('jobs.id', 'like', '%' . $request->text . '%');
                });
            });
        })
        ->join('job_applications', function($join) {
            $join->on('job_applications.job_id', '=', 'jobs.id')
                  ->whereNull('job_applications.deleted_at');
        })->join('users', function($join) {
            $join->on('users.id', '=', 'job_applications.user_id')
                  ->where('users.role', '=', 'WORKER')
                  ->whereNull('job_applications.deleted_at');
        })
        ->leftJoin('black_lists', function($join) {
            $join->on('black_lists.employee_id', '=', 'job_applications.user_id')
                  ->on('black_lists.company_id', '=', 'jobs.company_id');
        })
        ->leftJoin(DB::raw('(Select max(id) as id, status, company_id, employee_id from black_lists where deleted_at is null group by company_id, employee_id) LatestEmployee'), function($join) {
            $join->on('LatestEmployee.employee_id', '=', 'job_applications.user_id')
                 ->on('LatestEmployee.company_id', '=', 'jobs.company_id')
                 ->where('LatestEmployee.status', '=', 'ADDED');
        });
    }

    public function employees(Request $request) {
        $jobs = $this->tempTmployees($request);

        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        $activeJobs = clone $jobs;
        $inActionJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $activeJobs = $activeJobs->whereHas('firstGraphic',function($q){
            $q->whereDate('started_at', '>', date('Y-m-d'));
        })->where('is_active', 1)->groupBy('job_applications.id');

        $inActionJobs = $inActionJobs->where(function($q) {
            $q->where(function($q) {
                $q->whereHas('firstGraphic',function($q) {
                    $q->whereDate('started_at', '<=', date('Y-m-d'));
                })
                ->whereHas('lastGraphic',function($q){
                    $q->whereDate('finished_at', '>=', date('Y-m-d'));
                });
            });
        })->where('is_active', 1)
        ->groupBy('job_applications.id')
        // ->groupBy(['jobs.company_id', 'job_applications.id', 'LatestEmployee.id'])
        ;

        $historyJobs = $historyJobs->whereHas('lastGraphic',function($q){
            $q->whereDate('finished_at', '<', date('Y-m-d'));
        })->groupBy([
            'users.id'
        ]);


        $blackLists = BlackList::where('company_id', Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id)
                                    ->where('status', 'ADDED')
                                    ->with(['employee'])
                                    ->when($request->text, function ($q) use ($request) {
                                        return $q->where(function ($q) use ($request) {
                                            $q->whereHas('employee', function (Builder $q) use ($request) {
                                                $q->where('name', 'like', '%' . $request->text . '%');
                                            })->orWhere(function ($q) use ($request) {
                                                $q->where('reason', 'like', '%' . $request->text . '%');;
                                            });
                                        });
                                    });

        return Inertia::render('Company/Employees', [
            'title'             => __('general.collaborator'),
            'branches'          => Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id',  $companyId)->get(),
            'activeJobs'        => $activeJobs->get(),
            'inActionJobs'      => $inActionJobs->get(),
            'historyJobs'       => $historyJobs->get(),
            'activeJobsCount'   => $activeJobs->pluck('jobs.id')->count(),
            'inActionJobsCount' => $inActionJobs->pluck('jobs.id')->count(),
            'historyJobsCount'  => $historyJobs->get()->count(),
            'blackLists'        => $blackLists->get(),
            'blackListsCount'   => $blackLists->count(),
        ]);
    }

    public function getCompanyEmployees(Request $request) {
        try {
            $brancheIDS = isset($request->branche_ids) ? $request->branche_ids : [];

            $users = User::select('users.id', 'users.name', 'black_lists.status')->join('job_check_lists', function($join) {
                $join->on('job_check_lists.employee_user_id', '=', 'users.id')
                      ->where('job_check_lists.status', '=', 'CONFIRMED')
                      ->whereNull('job_check_lists.deleted_at');
                })->join('jobs', function($join) use($brancheIDS) {
                    $join->on('jobs.id', '=', 'job_check_lists.job_id')
                        ->whereIn('jobs.branche_id', $brancheIDS)
                        ->whereNull('jobs.deleted_at');
                })
                ->leftJoin('black_lists', function($query) {
                    $query->on('black_lists.employee_id','=','users.id')
                        ->whereNull('jobs.deleted_at')
                        ->whereRaw('black_lists.id IN (select MAX(a2.id) from black_lists as a2 join users as u2 on u2.id = a2.employee_id group by u2.id)');
                })
                ->where(function ($q) {
                    $q->where('black_lists.status', '=', 'REMOVED')
                      ->orWhereNull('black_lists.status');
                })
                ->groupBy('users.id')
                ->get();

        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_edited')], 500);
        }

        return response()->json(['users' => $users], 200);
    }

    public function getEmployeeBlackListStatuses(Request $request) {
        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        if((Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') && $request->company_id) {
            $companyId = $request->company_id;
        }

        $blackLists = BlackList::where('employee_id', $request->employee_id)
                                    ->where('company_id', $companyId)
                                    ->withTrashed()
                                    ->with([
                                        'createdUser' => function($q) {
                                            $q->select('id',  'name');
                                        }
                                    ])
                                    ->get();

        return response()->json(['status' => 'success', 'blackLists' => $blackLists], 200);
    }

    public function setBlackList(AddBlackListRequest $request) {
        try {
            DB::beginTransaction();

            $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

            $findBlackList = BlackList::where('company_id', $companyId)->where('status', $request->status)->where('employee_id', $request->employee_id)->first();

            if($findBlackList) {
                return response()->json(['message' => __('general.alreadyModified')], 500);
            }

            BlackList::where('company_id', $companyId)->where('employee_id', $request->employee_id)->delete();

            BlackList::create([
                'created_user_id' => Auth::user()->id,
                'status'          => $request->status,
                'company_id'      => $companyId,
                'employee_id'     => $request->employee_id,
                'reason'          => $request->reason
            ]);
            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function getEmployeeHistory(Request $request) {
        $historyList = $this->tempTmployees($request);

        $historyList = $historyList->whereHas('lastGraphic',function($q) {
            $q->whereDate('finished_at', '<', date('Y-m-d'));
        })->where('users.id', '=', $request->employee_id)->groupBy('job_applications.id')->orderBy('job_applications.created_at')->get();

        return response()->json(['status' => 'success', 'historyList' => $historyList], 200);
    }
}
