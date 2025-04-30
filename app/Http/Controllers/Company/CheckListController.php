<?php

namespace App\Http\Controllers\Company;

use DB;
use Log;
use Misc;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Company\AddCompanyJobGraphicRequest;
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
    App\Models\EmployeeBalance,
    App\Models\BonusHistory,
    App\Models\JobTimeGraphic;

class CheckListController extends Controller
{
    public function checkLists(Request $request) {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');
        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        $selectedBranches = $request->branches;
        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $selectedBranches = Misc::getModeratorBranches();
        }

        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $brancheIds = Misc::getModeratorBranches();
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->whereIn('id', $brancheIds)->get();
        } else {
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $companyId)->get();
        }

        $jobs = Job::select(
            'jobs.*',
            'job_applications.user_id as jb_user_id',
            'job_applications.created_at as jb_created_at',
            'ja_users.name as user_name',
            'jcl_users.name as jcl_user_name',
            'job_time_graphics.id as jtg_id',
            'job_applications.user_id as ja_user_id',
            'job_time_graphics.job_id as jtg_job_id',
            'job_time_graphics.lunch as jtg_lunch',
            'job_time_graphics.is_late_added as jtg_is_late_added',
            'job_time_graphics.started_at as jtg_started_at',
            'job_time_graphics.finished_at as jtg_finished_at',
            'job_time_graphics.started_at as jtg_old_started_at',
            'job_time_graphics.finished_at as jtg_old_finished_at',
            'job_time_graphics.lunch as jtg_old_lunch',
            'job_check_lists.id as jcl_id',
            'job_check_lists.status as jcl_status',
            'job_check_lists.lunch as jcl_lunch',
            'job_check_lists.started_at as jcl_started_at',
            'job_check_lists.finished_at as jcl_finished_at',
            'job_check_lists.created_at as jcl_created_at',
            DB::raw("ABS(company_balance_lists.amount) as cbl_amount"),
            DB::raw("SUM((TIMESTAMPDIFF(MINUTE, job_time_graphics.started_at, job_time_graphics.finished_at) / 60) - job_time_graphics.lunch / 60) as total_hours"),
            DB::raw("SUM((TIMESTAMPDIFF(MINUTE, job_check_lists.started_at, job_check_lists.finished_at) / 60) - job_check_lists.lunch / 60) as list_total_hours"),
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
            DB::raw("(CASE WHEN black_lists.status = 'ADDED' &&  CAST(job_time_graphics.finished_at as date) >=  CAST(black_lists.created_at as date) THEN 1 ELSE 0 END) AS bl_rows")
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
        ->where('jobs.user_id', $companyId)
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
        ->leftJoin('company_balance_lists', function($join) {
            $join
                ->on('company_balance_lists.job_check_list_id', '=', 'job_check_lists.id')
                ->on('company_balance_lists.employee_id', '=', 'job_applications.user_id')
                  ->whereNull('company_balance_lists.deleted_at');
        })
        ->leftjoin('users as jcl_users', function($join) {
            $join->on('jcl_users.id', '=', 'job_check_lists.employee_user_id')
                  ->whereNull('jcl_users.deleted_at');
        })
        ->leftJoin('black_lists', function($query) {
            $query->on('black_lists.employee_id', '=', 'job_applications.user_id')
                ->whereNull('jobs.deleted_at')
                ->whereRaw('black_lists.id IN (select MAX(a2.id) from black_lists as a2 join users as u2 on u2.id = a2.employee_id group by u2.id)');
        })
        ->when($selectedBranches, function ($q) use ($selectedBranches) {
            return $q->where(function ($q) use ($selectedBranches) {
                // $q->whereHas('branche', function (Builder $query) use ($request) {
                    $q->whereIn('jobs.branche_id', $selectedBranches);
                // });
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
        })->orderBy('jtg_finished_at', 'DESC');

        $waitingJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $waitingJobs = $waitingJobs
            ->whereDate('job_time_graphics.started_at', '<=', date('Y-m-d'))
            ->where('job_time_graphics.is_late_added', '=', 0)
            ->where(function($q) {
                $q->whereRaw("
                    CASE
                        WHEN jcl_users.id IS NOT NULL
                            THEN
                                job_check_lists.employee_user_id != jcl_users.id
                        ELSE job_check_lists.id IS NULL
                    END
                ");
            })
            // ->where(function ($q) {
            //     $q->where('black_lists.status', '=', 'REMOVED')
            //       ->orWhereNull('black_lists.status');
            // })
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

        return Inertia::render('Company/CheckLists', [
            'title'              => __('general.checkList'),
            'branches'           => $branches,
            'permissions'        => Permission::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'positions'          => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'shiftReasons'       => ShiftReason::select('id', 'type', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'establishments'     => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->whereIn('status', ['CONFIRMED', 'PASSIVATED'])->where('company_id', $companyId)->get(),
            'filterShiftReasons' => $filterShiftReasons,
            'waitingJobs'        => $waitingJobs,
            'historyJobs'        => $historyJobs,
            'waitingJobsCount'   => $waitingJobs->count(),
            'historyJobsCount'   => $historyJobs->count(),
            'startedAt'          => $startedAt,
            'finishedAt'         => $finishedAt,
        ]);
    }

    public function setCheckListStatus(Request $request) {
        $all         = $request['all'];
        $jobs        = $request['jobs'];
        $status      = $request->type == 'confirm' ? 'CONFIRMED' : 'CANCELED';
        $reasons     = [];
        $description = '';

        try {
            DB::beginTransaction();
            if($all == 'all') {
                $checkeds = $request['checkeds'];
                foreach($jobs as $job) {

                    foreach($checkeds as $check) {

                        $explodeCheck = explode("_", $check);

                        if($explodeCheck[0] == $job['jtg_id'] && $explodeCheck[1] == $job['jb_user_id']) {
                            $jobId            = $job['id'];
                            $jobTimeGraphicId = $job['jtg_id'];
                            $startedAt        = $job['jtg_started_at'];
                            $finishedAt       = $job['jtg_finished_at'];
                            $lunch            = $job['jtg_lunch'];
                            $employeeId       = $job['jb_user_id'];

                            if($status == 'CONFIRMED') {
                                $reasons          = isset($job['reasons']) ? $job['reasons'] : [];
                                $description      = isset($job['description']) ? $job['description'] : '';
                            } else {
                                $reasons          = isset($request->reasons) ? $request->reasons : [];
                                $description      = isset($request->description) ? $request->description : '';
                            }

                            $jobListData = $this->createJobCheckList($jobId, $jobTimeGraphicId, $status, $startedAt, $finishedAt, $reasons, $description, $employeeId, $lunch);
                            if(isset($jobListData['error'])) {
                                DB::rollBack();
                                return response()->json(['message' => $jobListData['message']], 500);
                            }
                        }

                    }


                }
            } else {
                $jobId            = $request->job['id'];
                $jobTimeGraphicId = $request->job['jtg_id'];
                $startedAt        = $request->job['jtg_started_at'];
                $finishedAt       = $request->job['jtg_finished_at'];
                $lunch            = $request->job['jtg_lunch'];
                $employeeId       = $request->job['jb_user_id'];

                if($status == 'CONFIRMED') {
                    $reasons          = isset($request->job['reasons']) ? $request->job['reasons'] : [];
                    $description      = isset($request->job['description']) ? $request->job['description'] : '';
                } else {
                    $reasons          = isset($request->reasons) ? $request->reasons : [];
                    $description      = isset($request->description) ? $request->description : '';
                }

                $jobListData = $this->createJobCheckList($jobId, $jobTimeGraphicId, $status, $startedAt, $finishedAt, $reasons, $description, $employeeId, $lunch);
                if(isset($jobListData['error'])) {
                    DB::rollBack();
                    return response()->json(['message' => $jobListData['message']], 500);
                }
            }

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function addCompanyJobGraphic(AddCompanyJobGraphicRequest $request) {
        try {
            $job = Job::select('id', 'company_id', 'branche_id')->where('id', $request->job_id)->first();

            if($job) {

                $firstJobTimeGraphics = JobTimeGraphic::where('job_id', $job->id)->orderBy('started_at', 'ASC')->first();

                if($firstJobTimeGraphics && date("Y-m-d", strtotime($request->started_at)) > date("Y-m-d")) {
                    return response()->json(['message' => __('general.checkGraphicDateNowValidation')], 500);
                }

                if($firstJobTimeGraphics && date("Y-m-d", strtotime($request->started_at)) < date("Y-m-d", strtotime($firstJobTimeGraphics->started_at))) {
                    return response()->json(['message' => __('general.scheduleLessFirstGraphicDayValidation')], 500);
                }

                $diffMinutes = round(abs(strtotime($request->started_at) - strtotime($request->finished_at)) / 60,2);

                if($request->lunch > $diffMinutes) {
                    return response()->json(['message' => __('general.checkGraphicAndLuncValidation')], 500);
                }

                $jobTimeGraphics = JobTimeGraphic::where('job_id', $job->id)->where(function ($q) use($request) {
                    $q->where(function ($t) use($request) {
                        $t->where('started_at', '>=', $request->started_at);
                        $t->where('finished_at', '<=', $request->finished_at)
                            ->whereNotNull('finished_at');
                    })
                    ->orWhere(function ($v) use($request) {
                        $v->where('started_at', '>=', $request->started_at);
                        $v->where('started_at', '<=', $request->finished_at)
                            ->whereNull('finished_at');
                    })
                    ->orWhere(function ($u) use($request) {
                        $u->where('started_at', '<=', $request->started_at);
                        $u->where('finished_at', '>=', $request->started_at)
                            ->whereNotNull('finished_at');
                    })
                    ->orWhere(function ($f) use($request) {
                        $f->where('started_at', '<=', $request->finished_at);
                        $f->where('finished_at', '>=', $request->finished_at)
                            ->whereNotNull('finished_at');
                    });
                })->get();

                if($jobTimeGraphics->isNotEmpty()) {
                    return response()->json(['message' => __('general.scheduleSameDateValidation')], 500);
                }

                $grapchic = JobTimeGraphic::create([
                    'job_id'        => $request->job_id,
                    'started_at'    => $request->started_at,
                    'finished_at'   => $request->finished_at,
                    'lunch'         => $request->lunch,
                    'is_late_added' => $request->is_late_added,
                ]);

                foreach($request->employeeIds as $employeeId) {
                    $this->createJobCheckList($job->id, $grapchic->id, 'CONFIRMED', $request->started_at, $request->finished_at, '', '', $employeeId, $request->lunch);
                }
            }
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function createJobCheckList($jobId, $jobTimeGraphicId, $status, $startedAt, $finishedAt, $reasons, $description, $employeeId, $lunch) {
        $findJob = Job::find($jobId);

        $salary = $findJob?->establishment?->salary;
        $tax = $findJob?->establishment?->tax ?? 0;
        $commission = $findJob?->establishment?->commission ?? 0;
        $bonus = 0;

        $timestamp1 = strtotime($startedAt);
        $timestamp2 = strtotime($finishedAt);

        $diffMinutes = round(abs($timestamp1 - $timestamp2) / 60,2);

        if(!$findJob->is_paid_lunch && $lunch > $diffMinutes) {
            return ['error' => true, 'message' => __('general.checkGraphicAndLuncValidation')];
        }

        $totalMinutes = abs($timestamp2 - $timestamp1) / (60);
        $totalMinutes = number_format((($totalMinutes - (!$findJob->is_paid_lunch ? $lunch : 0)) / 60), 4, '.', '');

        $totalSalary = $salary * $totalMinutes;
        $totalSalaryCompany = number_format((($salary + $tax + $commission) * ($totalMinutes)), 2, '.', '');

        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        if($findJob) {
            $jobCheckList = JobCheckList::create([
                'user_id'             => Auth::user()->id,
                'employee_user_id'    => $employeeId,
                'job_id'              => $jobId,
                'company_id'          => $findJob->company_id,
                'branche_id'          => $findJob->branche_id,
                'job_time_graphic_id' => $jobTimeGraphicId,
                'salary'              => $salary,
                'tax'                 => $tax,
                'commission'          => $commission,
                'status'              => $status,
                'lunch'               => $lunch,
                'is_paid_lunch'       => $findJob->is_paid_lunch,
                'started_at'          => $startedAt,
                'finished_at'         => $finishedAt,
            ]);

            if($status == 'CONFIRMED') {
                Misc::setEmployeeBalance('SHIFT', 'PLUS', $companyId, $jobId, $jobCheckList->id, $employeeId, $totalMinutes, $totalSalary);
                Misc::setCompanyBalance('SHIFT', 'MINUS', $companyId, $jobId, $jobCheckList->id, $employeeId, $totalMinutes, $totalSalaryCompany);
            }

            if($jobCheckList && $reasons) {
                $otherReason = ShiftReason::where('type', 'OTHER')->first();

                foreach($reasons as $reason) {
                    JobCheckListShiftReason::create([
                        'job_check_list_id' => $jobCheckList->id,
                        'shift_reason_id' => $reason,
                        'description' => $reason == $otherReason->id && $description ? $description : null,
                    ]);
                }
            }
        }

        return true;
    }

    public function checkListBonuses(Request $request) {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');
        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        $selectedBranches = $request->branches;
        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $selectedBranches = Misc::getModeratorBranches();
        }

        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $brancheIds = Misc::getModeratorBranches();
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->whereIn('id', $brancheIds)->get();
        } else {
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $companyId)->get();
        }

        $bonusHistories = BonusHistory::where('company_id', $companyId)
        ->with([
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
        ->when($selectedBranches, function ($q) use ($selectedBranches) {
            return $q->where(function ($qry) use ($selectedBranches) {
                $qry->whereHas('job', function (Builder $query) use ($selectedBranches) {
                    $query->whereIn('branche_id', $selectedBranches);
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
                    })->orWhere('id', 'like', '%' . $request->text . '%');;
                })->orWhereHas('job', function (Builder $query) use ($request) {
                    $query->whereHas('establishment', function (Builder $qr) use ($request) {
                        $qr->where('name', 'like', '%' . $request->text . '%');
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
        })
        ->orderBy('created_at', 'desc')
        ->paginate(50)
        ->appends(request()->query());

        return Inertia::render('Company/CheckListBonuses', [
            'title'          => __('general.checkListBonuses'),
            'bonusHistories' => $bonusHistories,
            'positions'      => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'branches'       => $branches,
            'establishments' => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->whereIn('status', ['CONFIRMED', 'PASSIVATED'])->where('company_id', $companyId)->get(),
            'startedAt'      => $startedAt,
            'finishedAt'     => $finishedAt
        ]);
    }
}
