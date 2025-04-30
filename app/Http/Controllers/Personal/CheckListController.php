<?php

namespace App\Http\Controllers\Personal;

use DB;
use Log;
use Auth;
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
    App\Models\JobApplication;

class CheckListController extends Controller
{
    private function _tempJobs($request) {
        return Job::select(
            'jobs.*',
            'job_applications.user_id as jb_user_id',
            'job_applications.created_at as jb_created_at',
            'ja_users.name as user_name',
            'jcl_users.name as jcl_user_name',
            'j_users.company_name as j_user_company_name',
            'job_time_graphics.id as jtg_id',
            'job_time_graphics.job_id as jtg_job_id',
            'job_time_graphics.started_at as jtg_started_at',
            'job_time_graphics.finished_at as jtg_finished_at',
            'job_check_lists.id as jcl_id',
            'job_check_lists.status as jcl_status',
            'job_check_lists.started_at as jcl_started_at',
            'job_check_lists.finished_at as jcl_finished_at',
            'job_check_lists.created_at as jcl_created_at',
            'employee_balance_lists.amount as ebl_amount',
            'employee_balance_lists.is_paid as ebl_is_paid',
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
            DB::raw("(CASE WHEN black_lists.status = 'ADDED' &&  CAST(job_time_graphics.finished_at as date) >=  CAST(black_lists.created_at as date) THEN 1 ELSE 0 END) AS bl_rows"),
            DB::raw("
                SUM(establishments.salary)
                *
                SUM(
                    CASE WHEN job_check_lists.started_at IS NOT NULL
                        THEN
                            (TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_check_lists.lunch ELSE 0 END))
                        ELSE
                            (TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END))
                    END
                / 60 )
                AS salary_waiting_total
            "),
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
                //   ->where('job_time_graphics.started_at', '<=', date('Y-m-d') . ' 23:59:59')
                  ->whereNull('job_time_graphics.deleted_at');
        })
        ->join('job_applications', function($join) {
            $join->on('job_applications.job_id', '=', 'jobs.id')
                  ->where('job_applications.user_id', '=', Auth::user()->id)
                  ->whereNull('job_applications.deleted_at');
        })->join('users as j_users', function($join) {
            $join->on('j_users.id', '=', 'jobs.company_id')
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
        })
        ->leftJoin('employee_balance_lists', function($join) {
            $join
                ->on('employee_balance_lists.job_check_list_id', '=', 'job_check_lists.id')
                ->on('employee_balance_lists.employee_id', '=', 'job_applications.user_id')
                  ->whereNull('employee_balance_lists.deleted_at');
        })
        ->leftjoin('users as jcl_users', function($join) {
            $join->on('jcl_users.id', '=', 'job_check_lists.user_id')
                  ->whereNull('jcl_users.deleted_at');
        })->leftJoin('black_lists', function($query) {
            $query->where('black_lists.employee_id', '=', Auth::user()->id)
                ->whereNull('jobs.deleted_at')
                ->whereRaw('black_lists.id IN (select MAX(a2.id) from black_lists as a2 join users as u2 on u2.id = a2.employee_id group by u2.id)');
        })->leftjoin('establishments', function($join) {
            $join->on('establishments.id', '=', 'jobs.establishment_id')
                  ->whereNull('establishments.deleted_at');
        })->when($request->branches, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('branche', function (Builder $query) use ($request) {
                    $query->whereIn('branche_id', $request->branches);
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
        })->when($request->companies, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('jobs.company_id', $request->companies);
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
        })->orderBy('jtg_finished_at', 'DESC');
    }

    public function checkLists(Request $request) {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');

        $jobs = $this->_tempJobs($request);

        $waitingJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $waitingJobs = $waitingJobs->whereHas('graphics',function($q) {
            $q->whereDate('started_at', '<=', date('Y-m-d'));
        })->where('job_time_graphics.is_late_added', '=', 0)
          ->whereNull('job_check_lists.id')
        //   ->where(function ($q) {
        //     $q->where(function($qry) {
        //         $qry->where('black_lists.status', '=', 'REMOVED')
        //             ->orWhereNull('black_lists.status');
        //     })->orWhere(function($qry) {
        //         $qry->where('black_lists.status', '=', 'ADDED');
        //     });

        //   })
          ->where('job_time_graphics.started_at', '<=', date('Y-m-d') . ' 23:59:59')
          ->groupBy('job_time_graphics.id')
          ->groupBy('job_applications.user_id')
          ->having('bl_rows', '<', '1')
          ;

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

        $waitingJobs = $waitingJobs->get();
        $historyJobs = $historyJobs->get();

        $totalWaitingJobs = $waitingJobs->sum('salary_waiting_total');
        $totalHistoryJobs = $historyJobs->sum('ebl_amount');

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

        $companies = JobApplication::select('users.id as ju_user_id', 'users.company_name as ju_user_company_name')->where('job_applications.user_id', Auth::user()->id)
                        ->join('jobs', function($join) {
                            $join->on('jobs.id', '=', 'job_applications.id')
                                ->whereNull('jobs.deleted_at');
                        })->join('users', function($join) {
                            $join->on('users.id', '=', 'jobs.user_id')
                                ->whereNull('users.deleted_at');
                        })->groupBy('users.id')->get();

        $estPosIDS = JobApplication::select('establishment_positions.position_id as est_position_id')->where('job_applications.user_id', Auth::user()->id)
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

        return Inertia::render('Personal/CheckLists', [
            'title'              => __('general.checkList'),
            'companies'          => $companies,
            'permissions'        => Permission::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'positions'          => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->whereIn('id', $estPosIDS)->get(),
            'shiftReasons'       => ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'establishments'     => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->where('user_id', Auth::user()->id)->get(),
            'filterShiftReasons' => $filterShiftReasons,
            'waitingJobs'        => $waitingJobs,
            'historyJobs'        => $historyJobs,
            'waitingJobsCount'   => $waitingJobs->count(),
            'historyJobsCount'   => $historyJobs->count(),
            'totalWaitingJobs'   => $totalWaitingJobs,
            'totalHistoryJobs'   => $totalHistoryJobs,
            'startedAt'          => $startedAt,
            'finishedAt'         => $finishedAt,
        ]);
    }

    public function schedule(Request $request) {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-t');

        $jobs = $this->_tempJobs($request);

        $waitingJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $waitingJobs = $waitingJobs->when($startedAt, function ($q) use ($startedAt) {
            return $q->where(function ($q) use ($startedAt) {
                $q->whereDate('job_time_graphics.started_at', '>=', $startedAt);
            });
        })->when($finishedAt, function ($q) use ($finishedAt) {
            return $q->where(function ($q) use ($finishedAt) {
                $q->whereDate('job_time_graphics.started_at', '<=', $finishedAt);
            });
        })
        ->where('job_time_graphics.is_late_added', '=', 0)
        ->whereNull('job_check_lists.id')
        ->groupBy('job_time_graphics.id')
        ->groupBy('job_applications.user_id')
        ->having('bl_rows', '<', '1');

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

        $waitingJobs = $waitingJobs->get();
        $historyJobs = $historyJobs->get();

        return Inertia::render('Personal/Schedule', [
            'title'       => __('general.schedule'),
            'waitingJobs' => $waitingJobs,
            'historyJobs' => $historyJobs,
        ]);
    }
}
