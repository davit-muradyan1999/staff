<?php

namespace App\Http\Controllers\Personal;

use DB;
use Auth;
use Log;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Job,
    App\Models\JobApplication,
    App\Models\Establishment,
    App\Models\Position,
    App\Models\JobFavorite;

class JobController extends Controller
{
    public function jobs(Request $request) {
        $jobs = Job::with([
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
                $query->select('id', 'title->ru as title_b', 'phone->ru as phone_b', 'address->ru as address_b', 'lat', 'lng');
            },
            'currency' => function($query) {
                $query->select('id', 'name');
            },
            'applications' => function($query) {
                $query->where('user_id', Auth::user()->id);
            },
            'graphics',
            'favorite'

        ])
        ->withCount([
            'graphics AS total_hours' => function ($query) {
                $query->select(DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, started_at, finished_at) / 60), 2) - lunch / 60)"));
            },
            'graphics AS total_minutes_without_lunch' => function ($query) {
                $query->select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, started_at, finished_at) - lunch)"));
            },
            'graphics AS total_minutes' => function ($query) {
                $query->select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, started_at, finished_at) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END))"));
            },
            'graphics AS total_days',
            'applications AS total_applications'
        ])
        ->whereHas('lastGraphic',function($q) {
            $q->whereDate('finished_at', '>=', date('Y-m-d'));
        })
        ->where(function($q) {
            $q->whereDoesntHave('applications')
              ->orWhereHas('applications',function($q) {
                $q->where('user_id', '!=', Auth::user()->id);
              });
        })
        ->when($request->branches, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('branche', function (Builder $query) use ($request) {
                    $query->whereIn('branche_id', $request->branches);
                });
            });
        })->when($request->establishments, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('establishment', function (Builder $query) use ($request) {
                    $query->whereIn('id', $request->establishments);
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
        })->when($request->isFavorite, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                if($request->isFavorite == 1) {
                    $q->has('favorite');
                }
            });
        })
        ->leftjoin('job_applications as ja_1', function($join) {
            $join->on('ja_1.job_id', '=', 'jobs.id')
                  ->whereNull('ja_1.deleted_at');
        })
        ->leftjoin('establishments as establishments_1', function($join) {
            $join->on('establishments_1.id', '=', 'jobs.establishment_id');
            $join->whereNull('establishments_1.deleted_at');
        })
        ->leftjoin('establishment_positions as establishment_positions_1', function($join) {
            $join->on('establishment_positions_1.establishment_id', '=', 'establishments_1.id');
            $join->whereNull('establishment_positions_1.deleted_at');
        })
        ->leftjoin('job_check_lists as jcl_1', function($join) {
            $join->on('jcl_1.company_id', '=', 'jobs.company_id')
                  ->where('jcl_1.employee_user_id', '=', Auth::user()->id)
                  ->where('jcl_1.status', '=', 'CONFIRMED')
                  ->whereNull('jcl_1.deleted_at');
        })
        ->leftjoin('jobs as jobs_2', function($join) {
            $join->on('jobs_2.id', '=', 'jcl_1.job_id');
            $join->whereNull('jobs_2.deleted_at');
        })
        ->leftjoin('establishments as establishments_2', function($join) {
            $join->on('establishments_2.id', '=', 'jobs_2.establishment_id');
            $join->whereNull('establishments_2.deleted_at');
        })
        ->leftjoin('establishment_positions as establishment_positions_2', function($join) {
            $join->on('establishment_positions_2.establishment_id', '=', 'establishments_2.id');
            $join->whereNull('establishment_positions_2.deleted_at');
        })
        ->leftjoin('job_special_employees as jse_1', function($join) {
            $join->on('jse_1.job_id', '=', 'jobs.id');
            $join->where('jse_1.user_id', '=', Auth::user()->id);
        })
        ->leftjoin('job_special_branches as jsb_1', function($join) {
            $join->on('jsb_1.job_id', '=', 'jobs.id');
        })
        ->leftJoin('black_lists', function($query) {
            $query->where('black_lists.employee_id', '=', Auth::user()->id)
                ->whereNull('jobs.deleted_at')
                ->whereRaw('black_lists.id IN (select MAX(a2.id) from black_lists as a2 join users as u2 on u2.id = a2.employee_id group by u2.id)');
        })
        ->whereRaw("
                CASE
                    WHEN jobs.job_type_id = 1 THEN jcl_1.id IS NOT NULL
                    WHEN jobs.job_type_id = 2 THEN establishment_positions_2.position_id = establishment_positions_1.position_id
                    WHEN jobs.job_type_id = 3 THEN jsb_1.id IS NOT NULL && jsb_1.branche_id = jcl_1.branche_id
                    WHEN jobs.job_type_id = 4 THEN establishment_positions_2.position_id = establishment_positions_1.position_id && jsb_1.id IS NOT NULL && jsb_1.branche_id = jcl_1.branche_id
                    WHEN jobs.job_type_id = 5 THEN jse_1.id IS NOT NULL
                    ELSE  jobs.id > 1
                END
        ")
        ->where(function ($q) {
            $q->where('black_lists.status', '=', 'REMOVED')
              ->orWhereNull('black_lists.status');
        })
        ->having('jobs.employee_qnt', '>', DB::raw('count(ja_1.id)'))
        ->orderBy('jobs.job_type_id', 'DESC')
        ->orderBy('jobs.id', 'DESC')
        ->groupBy('jobs.id');

        $activeJobsCount = clone $jobs;

        return Inertia::render('Personal/Jobs', [
            'title'           => __('general.jobs'),
            'jobs'            => $jobs->where('jobs.is_active', 1)->paginate(50)->appends(request()->query()),
            'activeJobsCount' => $activeJobsCount->where('jobs.is_active', 1)->count(),
            'positions'       => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'establishments'  => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->get(),
        ]);
    }

    public function acceptJob(Request $request) {
        try {
            if($request->application_id) {
                JobApplication::where('id', $request->application_id)->delete();
            } else {
                $job = Job::select('id', 'company_id')->where('id', $request->id)->first();

                if($job && $job->graphics) {
                    $checkSameTimeGraphics = $this->checkSameTimeGraphics($job->graphics);
                    if(!$checkSameTimeGraphics) {
                        return response()->json(['message' => __('messages.cannot_accept_job')], 500);
                    }

                    JobApplication::create([
                        'user_id'    => Auth::user()->id,
                        'job_id'     => $job->id,
                        'company_id' => $job->company_id,
                        'branche_id' => $job->branche_id,
                    ]);
                }
            }
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_edited')], 500);
        }

        return response()->json(['message' => __('general.successfully_edited')], 201);
    }

    public function checkSameTimeGraphics($jobGraphics) {
        $jobs = JobApplication::where('user_id', Auth::user()->id)
                    ->whereHas('job',function($q) {
                        $q->whereHas('graphics',function($q) {
                            $q->where('started_at', '>=', date('Y-m-d H:i:s'));
                        });
                    })->with([
                        'job' => function($q) {
                            $q->select('id')->with(['graphics']);
                        }
                    ])->groupBy('job_id')->get();

        if($jobs) {
            foreach($jobs as $job) {
                if($job?->job?->graphics) {
                    foreach($job->job->graphics as $graphic) {
                        foreach($jobGraphics as $jobGraphic) {
                            $start_1 = strtotime($jobGraphic->started_at);
                            $end_1 = strtotime($jobGraphic->finished_at);

                            $start_2 = strtotime($graphic->started_at);
                            $end_2 = strtotime($graphic->finished_at);

                            if(
                                ($start_1 <= $start_2 && $end_1 >= $end_2) ||
                                ($start_1 <= $end_2 && $start_1 >= $start_2) ||
                                ($end_1 >= $start_2 && $end_1 <= $end_2)
                            ) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function filterMap(Request $request) {
        try {
            $establishmentIDS = isset($request->data['establishments']) ? $request->data['establishments'] : [];
            $positionIDS = isset($request->data['positions']) ? $request->data['positions'] : [];
            $isFavorite = isset($request->data['isFavorite']) ? $request->data['isFavorite'] : false;

            $jobs = Job::with([
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
                    $query->select('id', 'title->ru as title_b', 'phone->ru as phone_b', 'address->ru as address_b', 'lat', 'lng');
                },
                'currency' => function($query) {
                    $query->select('id', 'name');
                },
                'advantageEmployees' => function($query) {
                    $query->select('id', 'job_id', 'advantage_employee_id')->with([
                        'advantageEmployee' => function($query) {
                            $query->select('id', 'name->'. app()->getLocale(). ' as name_e');
                        }
                    ]);
                },
                'graphics',
                'applications',
            ])->withCount([
                'graphics AS total_hours' => function ($query) {
                    $query->select(DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, started_at, finished_at) / 60), 2) - lunch / 60)"));
                },
                'graphics AS total_minutes_without_lunch' => function ($query) {
                    $query->select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, started_at, finished_at) - lunch)"));
                },
                'graphics AS total_minutes' => function ($query) {
                    $query->select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, started_at, finished_at) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END))"));
                },
                'graphics AS total_days'
            ])
            // ->whereHas('establishment', function (Builder $query) use ($establishmentIDS) {
            //     $query->whereIn('id', $establishmentIDS);
            // })
            ->whereHas('lastGraphic',function($q) {
                $q->whereDate('finished_at', '>=', date('Y-m-d'));
            })
            // ->whereHas('establishment', function (Builder $q) use ($positionIDS) {
            //     $q->whereHas('positions', function (Builder $query) use ($positionIDS) {
            //         $query->whereIn('position_id', $positionIDS);
            //     });
            // })
            ->when($positionIDS, function ($q) use ($positionIDS) {
                return $q->where(function ($q) use ($positionIDS) {
                    $q->whereHas('establishment', function (Builder $q) use ($positionIDS) {
                        $q->whereHas('positions', function (Builder $query) use ($positionIDS) {
                            $query->whereIn('position_id', $positionIDS);
                        });
                    });
                });
            })
            ->leftjoin('job_applications', function($join) {
                $join->on('job_applications.job_id', '=', 'jobs.id')
                      ->whereNull('job_applications.deleted_at');
            })
            ->when($isFavorite, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->has('favorite');
                });
            })
            ->leftjoin('job_applications as ja_1', function($join) {
                $join->on('ja_1.job_id', '=', 'jobs.id')
                      ->whereNull('ja_1.deleted_at');
            })
            ->leftjoin('establishments as establishments_1', function($join) {
                $join->on('establishments_1.id', '=', 'jobs.establishment_id');
                $join->whereNull('establishments_1.deleted_at');
            })
            ->leftjoin('establishment_positions as establishment_positions_1', function($join) {
                $join->on('establishment_positions_1.establishment_id', '=', 'establishments_1.id');
                $join->whereNull('establishment_positions_1.deleted_at');
            })
            ->leftjoin('job_check_lists as jcl_1', function($join) {
                $join->on('jcl_1.company_id', '=', 'jobs.company_id')
                      ->where('jcl_1.employee_user_id', '=', Auth::user()->id)
                      ->where('jcl_1.status', '=', 'CONFIRMED')
                      ->whereNull('jcl_1.deleted_at');
            })
            ->leftjoin('jobs as jobs_2', function($join) {
                $join->on('jobs_2.id', '=', 'jcl_1.job_id');
                $join->whereNull('jobs_2.deleted_at');
            })
            ->leftjoin('establishments as establishments_2', function($join) {
                $join->on('establishments_2.id', '=', 'jobs_2.establishment_id');
                $join->whereNull('establishments_2.deleted_at');
            })
            ->leftjoin('establishment_positions as establishment_positions_2', function($join) {
                $join->on('establishment_positions_2.establishment_id', '=', 'establishments_2.id');
                $join->whereNull('establishment_positions_2.deleted_at');
            })
            ->leftjoin('job_special_employees as jse_1', function($join) {
                $join->on('jse_1.job_id', '=', 'jobs.id');
                $join->where('jse_1.user_id', '=', Auth::user()->id);
            })
            ->leftjoin('job_special_branches as jsb_1', function($join) {
                $join->on('jsb_1.job_id', '=', 'jobs.id');
            })
            ->leftJoin('black_lists', function($query) {
                $query->where('black_lists.employee_id', '=', Auth::user()->id)
                    ->whereNull('jobs.deleted_at')
                    ->whereRaw('black_lists.id IN (select MAX(a2.id) from black_lists as a2 join users as u2 on u2.id = a2.employee_id group by u2.id)');
            })
            ->whereRaw("
                    CASE
                        WHEN jobs.job_type_id = 1 THEN jcl_1.id IS NOT NULL
                        WHEN jobs.job_type_id = 2 THEN establishment_positions_2.position_id = establishment_positions_1.position_id
                        WHEN jobs.job_type_id = 3 THEN jsb_1.id IS NOT NULL && jsb_1.branche_id = jcl_1.branche_id
                        WHEN jobs.job_type_id = 4 THEN establishment_positions_2.position_id = establishment_positions_1.position_id && jsb_1.id IS NOT NULL && jsb_1.branche_id = jcl_1.branche_id
                        WHEN jobs.job_type_id = 5 THEN jse_1.id IS NOT NULL
                        ELSE  jobs.id > 1
                    END
            ")
            ->where(function ($q) {
                $q->where('black_lists.status', '=', 'REMOVED')
                  ->orWhereNull('black_lists.status');
            })
            ->having('employee_qnt', '>', DB::raw('count(job_applications.id)'))
            ->groupBy('jobs.id')
            ->get();


        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_edited')], 500);
        }

        return response()->json(['data' => $jobs], 200);
    }

    public function showList($id) {
        $job = Job::with([
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
        // ->where('user_id', Auth::user()->id)
        ->withCount([
            'graphics AS total_hours' => function ($query) {
                $query->select(DB::raw("SUM(TRUNCATE((TIMESTAMPDIFF(MINUTE, started_at, finished_at) / 60), 2) - lunch / 60)"));
            },
            'graphics AS total_minutes_without_lunch' => function ($query) {
                $query->select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, started_at, finished_at) - lunch)"));
            },
            'graphics AS total_minutes' => function ($query) {
                $query->select(DB::raw("SUM(TIMESTAMPDIFF(MINUTE, started_at, finished_at) - (CASE WHEN jobs.is_paid_lunch = 0 THEN job_time_graphics.lunch ELSE 0 END))"));
            },
            'graphics AS total_days',
            'applications AS total_applications',
        ])->where('id', $id)->first();

        $title = $job?->branche?->title_b ? ' / ' . $job?->branche?->title_b . ' #' . $job->id : '';

        return Inertia::render('Personal/Job', [
            'title' => __('general.jobs') . $title,
            'job'   => $job,
        ]);
    }

    public function addToFavorite(Request $request) {
        $isFavorite = true;
        try {
            $checkFavorites = JobFavorite::where('job_id', $request->id)->where('employee_user_id', Auth::user()->id)->get();
            if($checkFavorites->isNotEmpty()) {
                JobFavorite::where('job_id', $request->id)->where('employee_user_id', Auth::user()->id)->delete();
                $isFavorite = false;
            } else {
                JobFavorite::create([
                    'job_id'           => $request->id,
                    'employee_user_id' => Auth::user()->id,
                ]);
            }
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added'), 'isFavorite' => $isFavorite], 201);
    }
}
