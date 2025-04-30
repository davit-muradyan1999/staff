<?php

namespace App\Http\Controllers\Admin;

use DB;
use Auth;
use Misc;
use Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use App\Models\User,
    App\Models\Branch,
    App\Models\Permission,
    App\Models\Position,
    App\Models\UserBranche,
    App\Models\UserPosition,
    App\Models\Job,
    App\Models\ShiftReason,
    App\Models\JobCheckList,
    App\Models\JobCheckListShiftReason,
    App\Models\Establishment,
    App\Models\BonusHistory,
    App\Models\EmployeeBalance,
    App\Models\EmployeeBalanceList,
    App\Models\CompanyBalance,
    App\Models\CompanyBalanceList;

class CheckListController extends Controller
{
    public function index(Request $request)
    {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');

        $isAdministrator = Auth::user()->role == 'ADMINISTRATOR' ? true : false;
        $onlySupportedUsers = Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;
        $onlySupportedCompanies = Auth::user()->role == 'ADMINISTRATOR' && in_array(8, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;

        $jobs = Job::select(
            'jobs.*',
            'job_applications.user_id as jb_user_id',
            'job_applications.created_at as jb_created_at',
            'ja_users.name as user_name',
            'jcl_users.name as jcl_user_name',
            'j_users.company_name as j_user_company_name',
            'job_time_graphics.id as jtg_id',
            'job_time_graphics.job_id as jtg_job_id',
            'job_time_graphics.lunch as jtg_lunch',
            'job_time_graphics.is_late_added as jtg_is_late_added',
            'job_time_graphics.started_at as jtg_started_at',
            'job_time_graphics.finished_at as jtg_finished_at',
            'job_check_lists.id as jcl_id',
            'job_check_lists.status as jcl_status',
            'job_check_lists.lunch as jcl_lunch',
            'job_check_lists.started_at as jcl_started_at',
            'job_check_lists.finished_at as jcl_finished_at',
            'job_check_lists.created_at as jcl_created_at',
            DB::raw("ABS(company_balance_lists.amount) as cbl_amount"),
            'employee_balance_lists.amount as ebl_amount',
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) / 60) - job_time_graphics.lunch / 60, 4)) as total_hours"),
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) / 60) - job_check_lists.lunch / 60, 4)) as list_total_hours"),

            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) - job_time_graphics.lunch as total_minutes_without_lunch
            "),
            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) -
                (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END)
                as total_minutes
            "),
            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) -
                (CASE WHEN jobs.is_paid_lunch = 0 THEN job_check_lists.lunch ELSE 0 END)
                as list_total_minutes
            "),

            DB::raw("
                SUM(establishments.salary + establishments.tax + establishments.commission)
                *
                SUM((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END))  / 60)
                AS cost_waiting_total
            "),

            DB::raw("SUM(ABS(company_balance_lists.amount)) AS cost_history_total"),

            DB::raw("
                SUM(establishments.salary)
                *
                SUM((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END)) / 60)
                AS salary_waiting_total
            "),

            DB::raw("SUM(employee_balance_lists.amount) AS salary_history_total")
        )->with([
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
            'graphics' => function($query) {
                $query->groupBy('id');
            },

        ])
        // ->where('jobs.user_id', Auth::user()->id)
        ->withCount([
            'graphics AS total_days',
            'applications AS total_applications',
        ])
        ->join('job_time_graphics', function($join) {
            $join->on('job_time_graphics.job_id', '=', 'jobs.id')
                  ->where('job_time_graphics.started_at', '<=', date('Y-m-d') . ' 23:59:59')
                  ->whereNull('job_time_graphics.deleted_at');
        })
        ->join('job_applications', function($join) {
            $join->on('job_applications.job_id', '=', 'jobs.id')
                  ->whereNull('job_applications.deleted_at');
        })->join('users as j_users', function($join) {
            $join->on('j_users.id', '=', 'jobs.user_id')
                  ->whereNull('j_users.deleted_at');
        })->join('users as ja_users', function($join) {
            $join->on('ja_users.id', '=', 'job_applications.user_id')
                  ->where('ja_users.role', '=', 'WORKER')
                  ->whereNull('job_applications.deleted_at');
        })->leftJoin('job_check_lists', function($join) {
            $join->on('job_check_lists.job_time_graphic_id', '=', 'job_time_graphics.id')
                  ->on('job_check_lists.employee_user_id', '=', 'job_applications.user_id')
                  ->whereNull('job_check_lists.deleted_at');
        })->leftJoin('job_check_list_shift_reasons', function($join) {
            $join->on('job_check_list_shift_reasons.job_check_list_id', '=', 'job_check_lists.id')
                  ->whereNull('job_check_list_shift_reasons.deleted_at');
        })->leftJoin('company_balance_lists', function($join) {
            $join
                ->on('company_balance_lists.job_check_list_id', '=', 'job_check_lists.id')
                ->on('company_balance_lists.employee_id', '=', 'job_applications.user_id')
                  ->whereNull('company_balance_lists.deleted_at');
        })->leftJoin('employee_balance_lists', function($join) {
            $join
                ->on('employee_balance_lists.job_check_list_id', '=', 'job_check_lists.id')
                ->on('employee_balance_lists.employee_id', '=', 'job_applications.user_id')
                  ->whereNull('employee_balance_lists.deleted_at');
        })->leftjoin('users as jcl_users', function($join) {
            $join->on('jcl_users.id', '=', 'job_check_lists.user_id')
                  ->whereNull('jcl_users.deleted_at');
        })->leftjoin('establishments', function($join) {
            $join->on('establishments.id', '=', 'jobs.establishment_id')
                  ->whereNull('establishments.deleted_at');
        })->when($request->company_id, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('jobs.company_id', $request->company_id);
            });
        })->when($request->branches, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('jobs.branche_id', $request->branches);
            });
        })->when($request->positions, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $q) use ($request) {
                    $q->whereHas('positions', function (Builder $query) use ($request) {
                        $query->whereIn('position_id', $request->positions);
                    });
                });
            });
        })->when($request->establishments, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $query) use ($request) {
                    $query->whereIn('id', $request->establishments);
                });
            });
        })->when($request->employees, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('applications', function (Builder $query) use ($request) {
                    $query->whereIn('user_id', $request->employees);
                });
            });
        })->when($request->text, function ($q) use ($request) {
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
        ->when($isAdministrator, function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
            return $q->where(function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
                if($onlySupportedUsers) {
                    $q->whereHas('applications', function (Builder $query) {
                        $query->whereHas('user', function (Builder $query) {
                            $query->whereHas('userSupports', function (Builder $query) {
                                $query->where('support_user_id', Auth::user()->id);
                            });
                        });
                    });
                }

                if($onlySupportedCompanies) {
                    if($onlySupportedUsers) {
                        $q->orWhereHas('companySupports', function (Builder $query) {
                            $query->where('support_user_id', Auth::user()->id);
                        });
                    } else {
                        $q->whereHas('companySupports', function (Builder $query) {
                            $query->where('support_user_id', Auth::user()->id);
                        });
                    }
                }
            });
        })
        ->orderBy('jtg_finished_at', 'DESC');

        $waitingJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $waitingJobs = $waitingJobs->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })->where('job_time_graphics.is_late_added', '=', 0)
          ->whereNull('job_check_lists.id')
          ->groupBy('job_time_graphics.id')
          ->groupBy('job_applications.user_id');

        $historyJobs = $historyJobs->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })->when($request->status, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('job_check_lists.status',  $request->status);
            });
        })->when($request->shiftReasons, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('job_check_list_shift_reasons.shift_reason_id',  $request->shiftReasons);
            });
        })->when($startedAt, function ($q) use ($startedAt) {
            return $q->where(function ($q) use ($startedAt) {
                $q->whereDate('job_check_lists.started_at', '>=', $startedAt);
            });
        })->when($finishedAt, function ($q) use ($finishedAt) {
            return $q->where(function ($q) use ($finishedAt) {
                $q->whereDate('job_check_lists.started_at', '<=', $finishedAt);
            });
        })
        ->whereNotNull('job_check_lists.id')
        ->groupBy(['job_check_lists.id']);

        $waitingJobsCount = $waitingJobs->pluck('job_time_graphics.id')->count();
        $historyJobsCount = $historyJobs->pluck('job_time_graphics.id')->count();

        $totalWaitingJobsCost = $waitingJobs->pluck('cost_waiting_total')->sum();
        $totalHistoryJobsCost = $historyJobs->pluck('cost_history_total')->sum();

        $totalWaitingJobsSalary = $waitingJobs->pluck('salary_waiting_total')->sum();
        $totalHistoryJobsSalary = $historyJobs->pluck('salary_history_total')->sum();

        $waitingJobs = $waitingJobs->paginate(50, ['*'], 'waiting_page')->appends(request()->query());
        $historyJobs = $historyJobs->paginate(50, ['*'], 'history_page')->appends(request()->query());

        if($historyJobs) {
            foreach($historyJobs as $job) {
                $jobCheckListShiftReason = JobCheckListShiftReason::select('job_check_list_id', 'shift_reason_id', 'description')->where('job_check_list_id', $job->jcl_id)->get();
                $job->shiftReasons = $jobCheckListShiftReason;
            }
        }

        $filterShiftReasons = [];
        $status = '';
        if($request->status == 'CONFIRMED') {
            $status = 'CONFIRMATION';
        } else if($request->status == 'CANCELED') {
            $status = 'CANCELED';
        }

        if($status) {
            $filterShiftReasons = ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->when($status, function ($q) use ($status) {
                return $q->where(function($q) use($status) {
                    $q->where('type', $status)->orWhere('type', 'OTHER');
                });
            })->get();
        }

        $companies = User::select('id', 'company_name')
                        ->where('role', 'COMPANY')->when($onlySupportedCompanies, function ($q) {
                            return $q->where(function ($q) {
                                $q->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        })->get();


        $employees = User::select('id', 'name')
                        ->where('role', 'WORKER')
                        ->when($onlySupportedUsers, function ($q) {
                            return $q->where(function ($q) {
                                $q->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        })->get();

        return Inertia::render('Admin/Libraries/CheckLists/List', [
            'title'                  => __('general.checkList'),
            'companies'              => $companies,
            'branches'               => Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])->get(),
            'permissions'            => Permission::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'positions'              => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'shiftReasons'           => ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'establishments'         => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->where('user_id', Auth::user()->id)->get(),
            'employees'              => $employees,
            'filterShiftReasons'     => $filterShiftReasons,
            'waitingJobs'            => $waitingJobs,
            'historyJobs'            => $historyJobs,
            'waitingJobsCount'       => $waitingJobsCount,
            'historyJobsCount'       => $historyJobsCount,

            'totalWaitingJobsCost' => $totalWaitingJobsCost,
            'totalHistoryJobsCost' => $totalHistoryJobsCost,

            'totalWaitingJobsSalary' => $totalWaitingJobsSalary,
            'totalHistoryJobsSalary' => $totalHistoryJobsSalary,

            'startedAt'              => $startedAt,
            'finishedAt'             => $finishedAt,
        ]);
    }

    public function income(Request $request)
    {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');

        $jobs = Job::select(
            'jobs.*',
            'job_applications.user_id as jb_user_id',
            'job_applications.created_at as jb_created_at',
            'ja_users.name as user_name',
            'jcl_users.name as jcl_user_name',
            'j_users.company_name as j_user_company_name',
            'job_time_graphics.id as jtg_id',
            'job_time_graphics.job_id as jtg_job_id',
            'job_time_graphics.lunch as jtg_lunch',
            'job_time_graphics.started_at as jtg_started_at',
            'job_time_graphics.finished_at as jtg_finished_at',
            'job_check_lists.id as jcl_id',
            'job_check_lists.status as jcl_status',
            'job_check_lists.lunch as jcl_lunch',
            'job_check_lists.started_at as jcl_started_at',
            'job_check_lists.finished_at as jcl_finished_at',
            'job_check_lists.created_at as jcl_created_at',
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) / 60), 2) - job_time_graphics.lunch / 60) as total_hours"),
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) / 60), 2) - job_check_lists.lunch / 60) as list_total_hours"),

            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) - job_time_graphics.lunch as total_minutes_without_lunch
            "),
            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) -
                (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END)
                as total_minutes
            "),
            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) -
                (CASE WHEN jobs.is_paid_lunch = 0 THEN job_check_lists.lunch ELSE 0 END)
                as list_total_minutes
            "),

            DB::raw("
                SUM((((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at)) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END)) / 60))
                *
                SUM(establishments.commission)

                as income_waiting_total
            "),
            DB::raw("
                SUM((((TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at)) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END)) / 60))
                *
                SUM(job_check_lists.commission)
                as income_history_total
            ")
        )->with([
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
            'graphics' => function($query) {
                $query->groupBy('id');
            },

        ])
        ->withCount([
            'graphics AS total_days',
            'applications AS total_applications',
        ])
        ->join('job_time_graphics', function($join) {
            $join->on('job_time_graphics.job_id', '=', 'jobs.id')
                  ->where('job_time_graphics.started_at', '<=', date('Y-m-d') . ' 23:59:59')
                  ->whereNull('job_time_graphics.deleted_at');
        })
        ->join('job_applications', function($join) {
            $join->on('job_applications.job_id', '=', 'jobs.id')
                  ->whereNull('job_applications.deleted_at');
        })->join('users as j_users', function($join) {
            $join->on('j_users.id', '=', 'jobs.user_id')
                  ->whereNull('j_users.deleted_at');
        })->join('users as ja_users', function($join) {
            $join->on('ja_users.id', '=', 'job_applications.user_id')
                  ->where('ja_users.role', '=', 'WORKER')
                  ->whereNull('job_applications.deleted_at');
        })->leftJoin('job_check_lists', function($join) {
            $join->on('job_check_lists.job_time_graphic_id', '=', 'job_time_graphics.id')
                 ->on('job_check_lists.employee_user_id', '=', 'job_applications.user_id')
                 ->whereNull('job_check_lists.deleted_at');
        })->leftJoin('job_check_list_shift_reasons', function($join) {
            $join->on('job_check_list_shift_reasons.job_check_list_id', '=', 'job_check_lists.id')
                  ->whereNull('job_check_list_shift_reasons.deleted_at');
        })->leftjoin('users as jcl_users', function($join) {
            $join->on('jcl_users.id', '=', 'job_check_lists.user_id')
                  ->whereNull('jcl_users.deleted_at');
        })->leftjoin('establishments', function($join) {
            $join->on('establishments.id', '=', 'jobs.establishment_id')
                  ->whereNull('establishments.deleted_at');
        })->when($request->branches, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('jobs.branche_id', $request->branches);
            });
        })->when($request->positions, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $q) use ($request) {
                    $q->whereHas('positions', function (Builder $query) use ($request) {
                        $query->whereIn('position_id', $request->positions);
                    });
                });
            });
        })->when($request->establishments, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $query) use ($request) {
                    $query->whereIn('id', $request->establishments);
                });
            });
        })->when($request->employees, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('applications', function (Builder $query) use ($request) {
                    $query->whereIn('user_id', $request->employees);
                });
            });
        })->when($request->text, function ($q) use ($request) {
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
                    $q->where('jobs.id', 'like', '%' . $request->text . '%');;
                });
            });
        })->orderBy('jtg_finished_at', 'DESC');;

        $waitingJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $waitingJobs = $waitingJobs->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })->where('job_time_graphics.is_late_added', '=', 0)
          ->whereNull('job_check_lists.id')
          ->groupBy('job_time_graphics.id')
          ->groupBy('job_applications.user_id');

        $historyJobs = $historyJobs->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })->when($request->status, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('job_check_lists.status',  $request->status);
            });
        })->when($request->shiftReasons, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('job_check_list_shift_reasons.shift_reason_id',  $request->shiftReasons);
            });
        })->when($startedAt, function ($q) use ($startedAt) {
            return $q->where(function ($q) use ($startedAt) {
                $q->whereDate('job_check_lists.started_at', '>=', $startedAt);
            });
        })->when($finishedAt, function ($q) use ($finishedAt) {
            return $q->where(function ($q) use ($finishedAt) {
                $q->whereDate('job_check_lists.started_at', '<=', $finishedAt);
            });
        })
        ->whereNotNull('job_check_lists.id')
        ->groupBy('job_check_lists.id');

        $waitingJobsCount = $waitingJobs->pluck('job_time_graphics.id')->count();
        $historyJobsCount = $historyJobs->pluck('job_time_graphics.id')->count();

        $totalWaitingJobsIncome = $waitingJobs->pluck('income_waiting_total')->sum();
        $totalHistoryJobsIncome = $historyJobs->pluck('income_history_total')->sum();

        $waitingJobs = $waitingJobs->paginate(50, ['*'], 'waiting_page')->appends(request()->query());
        $historyJobs = $historyJobs->paginate(50, ['*'], 'history_page')->appends(request()->query());

        if($historyJobs) {
            foreach($historyJobs as $job) {
                $jobCheckListShiftReason = JobCheckListShiftReason::select('job_check_list_id', 'shift_reason_id', 'description')->where('job_check_list_id', $job->jcl_id)->get();
                $job->shiftReasons = $jobCheckListShiftReason;
            }
        }


        $filterShiftReasons = [];
        $status = '';
        if($request->status == 'CONFIRMED') {
            $status = 'CONFIRMATION';
        } else if($request->status == 'CANCELED') {
            $status = 'CANCELED';
        }

        if($status) {
            $filterShiftReasons = ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->when($status, function ($q) use ($status) {
                return $q->where(function($q) use($status) {
                    $q->where('type', $status)->orWhere('type', 'OTHER');
                });
            })->get();
        }

        return Inertia::render('Admin/Libraries/CheckLists/Income', [
            'title'                  => __('general.income'),
            'companies'              => User::select('id', 'company_name')->where('role', 'COMPANY')->get(),
            'branches'               => Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])->get(),
            'permissions'            => Permission::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'positions'              => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'shiftReasons'           => ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'establishments'         => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->where('user_id', Auth::user()->id)->get(),
            'employees'              => User::select('id', 'name')->where('role', 'WORKER')->get(),
            'filterShiftReasons'     => $filterShiftReasons,
            'waitingJobs'            => $waitingJobs,
            'historyJobs'            => $historyJobs,
            'waitingJobsCount'       => $waitingJobsCount,
            'historyJobsCount'       => $historyJobsCount,
            'totalWaitingJobsIncome' => $totalWaitingJobsIncome,
            'totalHistoryJobsIncome' => $totalHistoryJobsIncome,
            'startedAt'              => $startedAt,
            'finishedAt'             => $finishedAt,
        ]);
    }

    public function spending(Request $request)
    {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');

        $jobs = Job::select(
            'jobs.*',
            'job_applications.user_id as jb_user_id',
            'job_applications.created_at as jb_created_at',
            'ja_users.name as user_name',
            'jcl_users.name as jcl_user_name',
            'j_users.company_name as j_user_company_name',
            'job_time_graphics.id as jtg_id',
            'job_time_graphics.job_id as jtg_job_id',
            'job_time_graphics.lunch as jtg_lunch',
            'job_time_graphics.started_at as jtg_started_at',
            'job_time_graphics.finished_at as jtg_finished_at',
            'job_check_lists.id as jcl_id',
            'job_check_lists.status as jcl_status',
            'job_check_lists.lunch as jcl_lunch',
            'job_check_lists.started_at as jcl_started_at',
            'job_check_lists.finished_at as jcl_finished_at',
            'job_check_lists.created_at as jcl_created_at',
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) / 60), 2) - job_time_graphics.lunch / 60) as total_hours"),
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) / 60), 2) - job_check_lists.lunch / 60) as list_total_hours"),

            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) - job_time_graphics.lunch as total_minutes_without_lunch
            "),
            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) -
                (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END)
                as total_minutes
            "),
            DB::raw("
                TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) -
                (CASE WHEN jobs.is_paid_lunch = 0 THEN job_check_lists.lunch ELSE 0 END)
                as list_total_minutes
            "),

            DB::raw("
                SUM((((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at)) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END)) / 60))
                *
                SUM(establishments.tax)

                as spending_waiting_total
            "),
            DB::raw("
                SUM((((TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at)) - job_check_lists.lunch) / 60))
                *
                SUM(job_check_lists.tax)
                as spending_history_total
            ")
        )->with([
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
            'graphics' => function($query) {
                $query->groupBy('id');
            },

        ])
        // ->where('jobs.user_id', Auth::user()->id)
        ->withCount([
            'graphics AS total_days',
            'applications AS total_applications',
        ])
        ->join('job_time_graphics', function($join) {
            $join->on('job_time_graphics.job_id', '=', 'jobs.id')
                  ->where('job_time_graphics.started_at', '<=', date('Y-m-d') . ' 23:59:59')
                  ->whereNull('job_time_graphics.deleted_at');
        })
        ->join('job_applications', function($join) {
            $join->on('job_applications.job_id', '=', 'jobs.id')
                  ->whereNull('job_applications.deleted_at');
        })->join('users as j_users', function($join) {
            $join->on('j_users.id', '=', 'jobs.user_id')
                  ->whereNull('j_users.deleted_at');
        })->join('users as ja_users', function($join) {
            $join->on('ja_users.id', '=', 'job_applications.user_id')
                  ->where('ja_users.role', '=', 'WORKER')
                  ->whereNull('job_applications.deleted_at');
        })->leftJoin('job_check_lists', function($join) {
            $join->on('job_check_lists.job_time_graphic_id', '=', 'job_time_graphics.id')
                 ->on('job_check_lists.employee_user_id', '=', 'job_applications.user_id')
                 ->whereNull('job_check_lists.deleted_at');
        })->leftJoin('job_check_list_shift_reasons', function($join) {
            $join->on('job_check_list_shift_reasons.job_check_list_id', '=', 'job_check_lists.id')
                  ->whereNull('job_check_list_shift_reasons.deleted_at');
        })->leftjoin('users as jcl_users', function($join) {
            $join->on('jcl_users.id', '=', 'job_check_lists.user_id')
                  ->whereNull('jcl_users.deleted_at');
        })->leftjoin('establishments', function($join) {
            $join->on('establishments.id', '=', 'jobs.establishment_id')
                  ->whereNull('establishments.deleted_at');
        })->when($request->branches, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('jobs.branche_id', $request->branches);
            });
        })->when($request->employees, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('applications', function (Builder $query) use ($request) {
                    $query->whereIn('user_id', $request->employees);
                });
            });
        })->when($request->positions, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $q) use ($request) {
                    $q->whereHas('positions', function (Builder $query) use ($request) {
                        $query->whereIn('position_id', $request->positions);
                    });
                });
            });
        })->when($request->establishments, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $query) use ($request) {
                    $query->whereIn('id', $request->establishments);
                });
            });
        })->when($request->text, function ($q) use ($request) {
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
                    $q->where('jobs.id', 'like', '%' . $request->text . '%');;
                });
            });
        })->orderBy('jtg_finished_at', 'DESC');;

        $waitingJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $waitingJobs = $waitingJobs->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })->where('job_time_graphics.is_late_added', '=', 0)
          ->whereNull('job_check_lists.id')
          ->groupBy('job_time_graphics.id')
          ->groupBy('job_applications.user_id');

        $historyJobs = $historyJobs->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })->when($request->status, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('job_check_lists.status',  $request->status);
            });
        })->when($request->shiftReasons, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('job_check_list_shift_reasons.shift_reason_id',  $request->shiftReasons);
            });
        })->when($startedAt, function ($q) use ($startedAt) {
            return $q->where(function ($q) use ($startedAt) {
                $q->whereDate('job_check_lists.started_at', '>=', $startedAt);
            });
        })->when($finishedAt, function ($q) use ($finishedAt) {
            return $q->where(function ($q) use ($finishedAt) {
                $q->whereDate('job_check_lists.started_at', '<=', $finishedAt);
            });
        })
        ->whereNotNull('job_check_lists.id')
        ->groupBy('job_check_lists.id');

        $waitingJobsCount = $waitingJobs->pluck('job_time_graphics.id')->count();
        $historyJobsCount = $historyJobs->pluck('job_time_graphics.id')->count();

        $totalWaitingJobsSpending = $waitingJobs->pluck('spending_waiting_total')->sum();
        $totalHistoryJobsSpending = $historyJobs->pluck('spending_history_total')->sum();

        $waitingJobs = $waitingJobs->paginate(50, ['*'], 'waiting_page')->appends(request()->query());
        $historyJobs = $historyJobs->paginate(50, ['*'], 'history_page')->appends(request()->query());

        if($historyJobs) {
            foreach($historyJobs as $job) {
                $jobCheckListShiftReason = JobCheckListShiftReason::select('job_check_list_id', 'shift_reason_id', 'description')->where('job_check_list_id', $job->jcl_id)->get();
                $job->shiftReasons = $jobCheckListShiftReason;
            }
        }

        $filterShiftReasons = [];
        $status = '';
        if($request->status == 'CONFIRMED') {
            $status = 'CONFIRMATION';
        } else if($request->status == 'CANCELED') {
            $status = 'CANCELED';
        }

        if($status) {
            $filterShiftReasons = ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->when($status, function ($q) use ($status) {
                return $q->where(function($q) use($status) {
                    $q->where('type', $status)->orWhere('type', 'OTHER');
                });
            })->get();
        }

        return Inertia::render('Admin/Libraries/CheckLists/Spending', [
            'title'                    => __('general.spending'),
            'companies'                => User::select('id', 'company_name')->where('role', 'COMPANY')->get(),
            'branches'                 => Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])->get(),
            'permissions'              => Permission::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'positions'                => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'shiftReasons'             => ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'establishments'           => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->where('user_id', Auth::user()->id)->get(),
            'employees'                => User::select('id', 'name')->where('role', 'WORKER')->get(),
            'filterShiftReasons'       => $filterShiftReasons,
            'waitingJobs'              => $waitingJobs,
            'historyJobs'              => $historyJobs,
            'waitingJobsCount'         => $waitingJobsCount,
            'historyJobsCount'         => $historyJobsCount,
            'totalWaitingJobsSpending' => $totalWaitingJobsSpending,
            'totalHistoryJobsSpending' => $totalHistoryJobsSpending,
            'startedAt'                => $startedAt,
            'finishedAt'               => $finishedAt,
        ]);
    }

    public function checkListBonuses(Request $request) {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');

        $isAdministrator = Auth::user()->role == 'ADMINISTRATOR' ? true : false;
        $onlySupportedUsers = Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;
        $onlySupportedCompanies = Auth::user()->role == 'ADMINISTRATOR' && in_array(8, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;

        $jobs = Job::select(
            'jobs.*',
            'job_applications.user_id as jb_user_id',
            'job_applications.created_at as jb_created_at',
            'ja_users.name as user_name',
            'job_applications.user_id as ja_users_id',
            'jcl_users.name as jcl_user_name',
            'job_time_graphics.id as jtg_id',
            'job_time_graphics.job_id as jtg_job_id',
            'job_time_graphics.lunch as jtg_lunch',
            'job_time_graphics.started_at as jtg_started_at',
            'job_time_graphics.finished_at as jtg_finished_at',
            'job_time_graphics.started_at as jtg_old_started_at',
            'job_time_graphics.finished_at as jtg_old_finished_at',
            'job_check_lists.id as jcl_id',
            'job_check_lists.status as jcl_status',
            'job_check_lists.lunch as jcl_lunch',
            'job_check_lists.started_at as jcl_started_at',
            'job_check_lists.finished_at as jcl_finished_at',
            'job_check_lists.created_at as jcl_created_at',
            'bonus_histories.status as bh_status',
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) / 60), 2) - job_time_graphics.lunch / 60) as total_hours"),
            DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) / 60), 2) - job_check_lists.lunch / 60) as list_total_hours"),
            DB::raw("TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) - job_time_graphics.lunch as total_minutes"),
            DB::raw("TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) - job_check_lists.lunch as list_total_minutes")
        )->with([
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
            'graphics' => function($query) {
                $query->groupBy('id');
            },

        ])
        ->whereNotNull('jobs.bonus')
        ->whereHas('lastGraphic',function($q){
            $q->whereDate('finished_at', '<', date('Y-m-d'));
        })
        ->withCount([
            'graphics AS total_days',
            'applications AS total_applications',
        ])
        ->join('job_time_graphics', function($join) {
            $join->on('job_time_graphics.job_id', '=', 'jobs.id')
                  ->whereNull('job_time_graphics.deleted_at');
        })
        ->join('job_applications', function($join) {
            $join->on('job_applications.job_id', '=', 'jobs.id')
                  ->whereNull('job_applications.deleted_at');
        })
        ->join('users as ja_users', function($join) {
            $join->on('ja_users.id', '=', 'job_applications.user_id')
                  ->where('ja_users.role', '=', 'WORKER')
                  ->whereNull('ja_users.deleted_at');
        })
        ->leftJoin('job_check_lists', function($join) {
            $join
                ->on('job_check_lists.job_time_graphic_id', '=', 'job_time_graphics.id')
                ->on('job_check_lists.job_id', '=', 'jobs.id')
                ->on('job_check_lists.employee_user_id', '=', 'job_applications.user_id')
                  ->whereNull('job_check_lists.deleted_at');
        })
        ->leftJoin('job_check_list_shift_reasons', function($join) {
            $join->on('job_check_list_shift_reasons.job_check_list_id', '=', 'job_check_lists.id')
                  ->whereNull('job_check_list_shift_reasons.deleted_at');
        })
        ->leftjoin('users as jcl_users', function($join) {
            $join->on('jcl_users.id', '=', 'job_check_lists.employee_user_id')
                  ->whereNull('jcl_users.deleted_at');
        })
        ->leftjoin('bonus_histories', function($join) {
            $join->on('bonus_histories.job_id', '=', 'jobs.id')
                 ->on('bonus_histories.employee_id', '=', 'job_applications.user_id')
                 ->whereNull('bonus_histories.deleted_at');
        })
        ->when($request->company_id, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('jobs.company_id', $request->company_id);
            });
        })
        ->when($request->employees, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('job_applications.user_id', $request->employees);
            });
        })
        ->when($request->branches, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('jobs.branche_id', $request->branches);
            });
        })->when($request->positions, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $q) use ($request) {
                    $q->whereHas('positions', function (Builder $query) use ($request) {
                        $query->whereIn('position_id', $request->positions);
                    });
                });
            });
        })->when($request->establishments, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $query) use ($request) {
                    $query->whereIn('id', $request->establishments);
                });
            });
        })->when($request->text, function ($q) use ($request) {
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
                        $q->where('jobs.id', 'like', '%' . $request->text . '%');;
                    });
                });
        })
        ->when($isAdministrator, function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
            return $q->where(function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
                if($onlySupportedUsers) {
                    $q->whereHas('applications', function (Builder $query) {
                        $query->whereHas('user', function (Builder $query) {
                            $query->whereHas('userSupports', function (Builder $query) {
                                $query->where('support_user_id', Auth::user()->id);
                            });
                        });
                    });
                }

                if($onlySupportedCompanies) {
                    if($onlySupportedUsers) {
                        $q->orWhereHas('companySupports', function (Builder $query) {
                            $query->where('support_user_id', Auth::user()->id);
                        });
                    } else {
                        $q->whereHas('companySupports', function (Builder $query) {
                            $query->where('support_user_id', Auth::user()->id);
                        });
                    }
                }
            });
        })
        ->whereNull('bonus_histories.job_id');

        $waitingJobs = clone $jobs;


        $waitingJobs = $waitingJobs
        ->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })
        ->where('job_time_graphics.is_late_added', '=', 0)
        // ->where(function($q) {
        //     $q->whereRaw("
        //         CASE
        //             WHEN  jcl_users.id IS NOT NULL
        //                 THEN
        //                     job_check_lists.employee_user_id != jcl_users.id &&
        //                     job_check_lists.job_time_graphic_id != 161
        //             ELSE job_check_lists.id IS NULL
        //         END
        //     ");
        // })
        ->groupBy('jobs.id')
        ->groupBy('job_applications.user_id')
        ;

        $waitingJobsCount = $waitingJobs->pluck('jobs.id')->count();

        $waitingJobs = $waitingJobs->orderBy('jtg_finished_at', 'DESC')->paginate(50)->appends(request()->query());

        $histories = BonusHistory::
            with([
                'company' => function($query) {
                    $query->select('id', 'name');
                },
                'employee' => function($query) {
                    $query->select('id', 'name');
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
            ])
            ->when($request->status, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->where('status', $request->status);
                });
            })
            ->when($request->establishments, function ($q) use ($request) {
                return $q->where(function ($qry) use ($request) {
                    $qry->whereHas('job', function (Builder $query) use ($request) {
                        $query->whereHas('establishment', function (Builder $qr) use ($request) {
                            $qr->whereIn('id', $request->establishments);
                        });
                    });
                });
            })
            ->when($request->branches, function ($q) use ($request) {
                return $q->where(function ($qry) use ($request) {
                    $qry->whereHas('job', function (Builder $query) use ($request) {
                        $query->whereIn('branche_id', $request->branches);
                    });
                });
            })
            ->when($request->employees, function ($q) use ($request) {
                return $q->where(function ($qry) use ($request) {
                    $qry->whereHas('job', function (Builder $query) use ($request) {
                        $query->whereHas('applications', function (Builder $query) use ($request) {
                            $query->whereIn('user_id', $request->employees);
                        });
                    });
                });
            })
            ->when($request->text, function ($q) use ($request) {
                return $q->where(function ($qry) use ($request) {
                    $qry->whereHas('employee', function (Builder $query) use ($request) {
                        $query->where('name', 'like', '%' . $request->text . '%');
                    })->orwhereHas('job', function (Builder $query) use ($request) {
                        $query->whereHas('branche', function (Builder $qr) use ($request) {
                            $qr->where('title', 'like', '%' . $request->text . '%');
                        })->orWhere('id', 'like', '%' . $request->text . '%');
                    })->orWhereHas('job', function (Builder $query) use ($request) {
                        $query->whereHas('establishment', function (Builder $qr) use ($request) {
                            $qr->where('name', 'like', '%' . $request->text . '%');
                        });
                    });
                });
            })

            ->when($isAdministrator, function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
                return $q->where(function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {

                    if($onlySupportedUsers) {
                        $q->whereHas('employee', function (Builder $query) {
                            $query->whereHas('userSupports', function (Builder $query) {
                                $query->where('support_user_id', Auth::user()->id);
                            });
                        });
                    }

                    if($onlySupportedCompanies) {
                        if($onlySupportedUsers) {
                            $q->orWhereHas('company', function (Builder $query) {
                                $query->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        } else {
                            $q->whereHas('company', function (Builder $query) {
                                $query->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        }
                    }
                });
            })

            ->when($onlySupportedUsers, function ($q) use ($onlySupportedUsers) {
                return $q->where(function ($q) use ($onlySupportedUsers) {
                    $q->whereHas('employee', function (Builder $query) use ($onlySupportedUsers) {
                        $query->whereHas('userSupports', function (Builder $query) use ($onlySupportedUsers) {
                            $query->where('support_user_id', Auth::user()->id);
                        });
                    });
                });
            })

            ->when($startedAt, function ($q) use ($startedAt) {
                return $q->where(function ($q) use ($startedAt) {
                    $q->whereDate('created_at', '>=', $startedAt);
                });
            })
            ->when($finishedAt, function ($q) use ($finishedAt) {
                return $q->where(function ($q) use ($finishedAt) {
                    $q->whereDate('created_at', '<=', $finishedAt);
                });
            })->orderBy('created_at', 'desc');

        $bonusHistories = clone $histories;
        $bonusHistoriesCount = $histories->count();
        $bonusHistories = $bonusHistories->paginate(50)->appends(request()->query());

        $companies = User::select('id', 'company_name')
                        ->where('role', 'COMPANY')->when($onlySupportedCompanies, function ($q) {
                            return $q->where(function ($q) {
                                $q->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        })->get();


        $employees = User::select('id', 'name')
                        ->where('role', 'WORKER')
                        ->when($onlySupportedUsers, function ($q) {
                            return $q->where(function ($q) {
                                $q->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        })->get();

        $filterShiftReasons = [];
        return Inertia::render('Admin/Libraries/CheckLists/Bonuses', [
            'title'               => __('general.checkListBonuses'),
            'companies'           => $companies,
            'branches'            => Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])->get(),
            'permissions'         => Permission::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'positions'           => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'shiftReasons'        => ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'establishments'      => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->get(),
            'employees'           => $employees,
            'filterShiftReasons'  => $filterShiftReasons,
            'waitingJobs'         => $waitingJobs,
            'waitingJobsCount'    => $waitingJobsCount,
            'startedAt'           => $startedAt,
            'finishedAt'          => $finishedAt,
            'bonusHistories'      => $bonusHistories,
            'bonusHistoriesCount' => $bonusHistoriesCount,
        ]);
    }

    public function setCheckListBonuses(Request $request) {
        try {

            if(Auth::user()->role == 'ADMINISTRATOR')
                return response()->json(['message' => __('general.not_added')], 500);

            $findHistory = BonusHistory::where('job_id', $request->job_id)->where('employee_id', $request->employee_id)->first();
            if($findHistory) {
                return response()->json(['message' => __('general.not_added')], 500);
            }

            BonusHistory::create([
                'status'          => $request->status,
                'created_user_id' => Auth::user()->id,
                'company_id'      => $request->company_id,
                'employee_id'     => $request->employee_id,
                'job_id'          => $request->job_id,
                'amount'          => $request->amount ? $request->amount : 0,
                'reason'          => $request->reason ? $request->reason : '',
            ]);

            if($request->status == 'CONFIRMED') {
                Misc::setEmployeeBalance('BONUS', 'PLUS', $request->company_id, $request->job_id, null, $request->employee_id, 0, $request->amount);
                Misc::setCompanyBalance('BONUS', 'MINUS', $request->company_id, $request->job_id, null, $request->employee_id, 0, $request->amount);
            }

        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
