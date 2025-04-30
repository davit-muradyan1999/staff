<?php

namespace App\Http\Controllers\Personal;

use DB;
use Auth;
use Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Job,
    App\Models\Establishment,
    App\Models\User,
    App\Models\Branch;

class ActiveWorkController extends Controller
{
    public function activeWorks(Request $request) {
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
            'applications AS total_applications',
        ])
        ->when($request->company_id, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('jobs.company_id', $request->company_id);
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
                    $q->where('id', 'like', '%' . $request->text . '%');
                });
            });
        })->leftJoin('black_lists', function($query) {
            $query->where('black_lists.employee_id', '=', Auth::user()->id)
                ->whereNull('jobs.deleted_at')
                ->whereRaw('black_lists.id IN (select MAX(a2.id) from black_lists as a2 join users as u2 on u2.id = a2.employee_id group by u2.id)');
        })->orderBy('jobs.id', 'desc');

        if(Auth::user()->role == 'WORKER') {
            $jobs = $jobs->whereHas('applications', function($q) {
                $q->where('user_id', '=', Auth::user()->id);
            });
        } else {
            $jobs = $jobs->has('applications');
        }

        $bidsJobs = clone $jobs;
        $currentJobs = clone $jobs;
        $historyJobs = clone $jobs;


        $isAdministrator = Auth::user()->role == 'ADMINISTRATOR' ? true : false;
        $onlySupportedUsers = Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;
        $onlySupportedCompanies = Auth::user()->role == 'ADMINISTRATOR' && in_array(8, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;

        $bidsJobs = $bidsJobs->whereHas('firstGraphic',function($q) {
            $q->whereDate('started_at', '>', date('Y-m-d'));
        })
        ->where('is_active', 1)
        ->where(function ($q) {
            $q->where('black_lists.status', '=', 'REMOVED')
              ->orWhereNull('black_lists.status');
        });

        $currentJobs = $currentJobs->where(function($q) {
            $q->where(function($q) {
                $q->whereHas('firstGraphic',function($q) {
                    $q->whereDate('started_at', '<=', date('Y-m-d'));
                })
                ->whereHas('lastGraphic',function($q){
                    $q->whereDate('finished_at', '>=', date('Y-m-d'));
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
        ->where('is_active', 1)
        ->where(function ($q) {
            $q->where('black_lists.status', '=', 'REMOVED')
              ->orWhereNull('black_lists.status');
        })
        ;

        $historyJobs = $historyJobs->whereHas('lastGraphic',function($q){
            $q->whereDate('finished_at', '<', date('Y-m-d'));
        })->when($isAdministrator, function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
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
        });

        $fileRenderName = 'ActiveWork';
        $companies = [];
        $employees = [];
        $establishments = Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e');
        $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', Auth::user()->id)->get();

        if(Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') {
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])->get();

            $companies = User::select('id', 'company_name')
                            ->where('role', 'COMPANY')
                            ->when($onlySupportedCompanies, function ($q) {
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
                            })
                            ->get();
            $fileRenderName = 'AdminActiveWork';
        }

        return Inertia::render('Personal/' . $fileRenderName, [
            'title'            => 'Мои должности',
            'companies'        => $companies,
            'branches'         => $branches,
            'employees'        => $employees,
            'establishments'   => $establishments,
            'bidsJobs'         => $bidsJobs->get(),
            'currentJobs'      => $currentJobs->get(),
            'historyJobs'      => $historyJobs->get(),
            'bidsJobsCount'    => $bidsJobs->count(),
            'currentJobsCount' => $currentJobs->count(),
            'historyJobsCount' => $historyJobs->count(),
        ]);
    }

}
