<?php

namespace App\Http\Controllers\Company;

use Log;
use Auth;
use Misc;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Company\AddCompanyJobRequest,
    App\Http\Requests\Company\AddCompanyJobGraphicRequest;
use Inertia\Inertia;
use App\Models\Job,
    App\Models\Branch,
    App\Models\Establishment,
    App\Models\Currency,
    App\Models\JobTimeGraphic,
    App\Models\JobApplication,
    App\Models\User,
    App\Models\JobType,
    App\Models\JobSpecialEmployee,
    App\Models\JobSpecialBranche,
    App\Models\Position,
    App\Models\AdvantageEmploye,
    App\Models\JobAdvantageEmployee;

class JobController extends Controller
{
    public function companyJobs(Request $request) {
        $selectedBranches = $request->branches;
        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $selectedBranches = Misc::getModeratorBranches();
        }

        $isAdministrator = Auth::user()->role == 'ADMINISTRATOR' ? true : false;
        $onlySupportedUsers = Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;
        $onlySupportedCompanies = Auth::user()->role == 'ADMINISTRATOR' && in_array(8, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;

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
            'user' => function($query) {
                $query->select('id', 'name');
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
            'jobType' => function($query) {
                $query->select('id', 'name->'. app()->getLocale(). ' as name_j');
            },
        ])
        ->withCount([
            'graphics AS total_hours' => function ($query) {
                $query->select(DB::raw("SUM((TIMESTAMPDIFF(MINUTE, started_at, finished_at) / 60) - lunch / 60)"));
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
                $q->where('company_id', $request->company_id);
            });
        })
        ->when($selectedBranches, function ($q) use ($selectedBranches) {
            return $q->where(function ($q) use ($selectedBranches) {
                $q->whereHas('branche', function (Builder $query) use ($selectedBranches) {
                    $query->whereIn('branche_id', $selectedBranches);
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
                    $q->where('id', 'like', '%' . $request->text . '%');;
                });
            });
        })
        ->when($isAdministrator, function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
            return $q->where(function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
                if($onlySupportedCompanies) {
                    $q->whereHas('companySupports', function (Builder $query) {
                        $query->where('support_user_id', Auth::user()->id);
                    });
                }
            });
        })
        ;

        $unacceptedJobs = clone $jobs;
        $activeJobs = clone $jobs;
        $inActionJobs = clone $jobs;
        $passiveJobs = clone $jobs;
        $historyJobs = clone $jobs;

        $unacceptedJobs = $unacceptedJobs->whereHas('lastGraphic',function($q){
            $q->whereDate('finished_at', '>=', date('Y-m-d'));
        })->whereDoesntHave('applications')->where('is_active', 1);

        $activeJobs = $activeJobs->whereHas('firstGraphic',function($q){
            $q->whereDate('started_at', '>', date('Y-m-d'));
        })->has('applications')->where('is_active', 1);

        $inActionJobs = $inActionJobs->where(function($q) {
            $q->where(function($q) {
                $q->whereHas('firstGraphic',function($q) {
                    $q->whereDate('started_at', '<=', date('Y-m-d'));
                })
                ->whereHas('lastGraphic',function($q){
                    $q->whereDate('finished_at', '>=', date('Y-m-d'));
                });
            });
        })->has('applications')->where('is_active', 1);

        $passiveJobs = $passiveJobs->where('is_active', 0);

        $historyJobs = $historyJobs->whereHas('lastGraphic',function($q) {
            $q->whereDate('finished_at', '<', date('Y-m-d'));
        })->where('is_active', 1)->orderBy('jobs.id', 'desc');

        if(Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') {
            $fileRenderName = 'AdminJobs';
            $establishments = Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->get();



            if($isAdministrator) {
                $companies = User::select('id', 'company_name')->where('role', 'COMPANY')
                    ->when($isAdministrator, function ($q) use ($onlySupportedCompanies) {
                        return $q->where(function ($q) use ($onlySupportedCompanies) {
                            if($onlySupportedCompanies) {
                                $q->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            }
                        });
                    })->get();

                    $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])
                        ->whereHas('user', function (Builder $query) {
                            $query->whereHas('userSupports', function (Builder $query) {
                                $query->where('support_user_id', Auth::user()->id);
                            });
                    })->get();
            } else {
                $companies = User::select('id', 'company_name')->where('role', 'COMPANY')->get();
                $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])->get();
            }
        } else {
            $fileRenderName = 'Jobs';
            $companyId = (Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id);
            if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
                $brancheIds = Misc::getModeratorBranches();
                $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->whereIn('id', $brancheIds)->get();
            } else {
                $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $companyId)->get();
            }
            $establishments = Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->where(function($q) {
                    $q->whereIn('status', ['CONFIRMED', 'PASSIVATED']);
                })->where('company_id', $companyId)
                 ->get();
            $companies = [];
        }

        return Inertia::render('Company/Jobs/' . $fileRenderName, [
            'title'               => __('general.jobs'),
            'companies'           => $companies,
            'branches'            => $branches,
            'establishments'      => $establishments,
            'currencies'          => Currency::select('id', 'name')->get(),
            'positions'           => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),

            'unacceptedJobs'      => $unacceptedJobs->get(),
            'activeJobs'          => $activeJobs->get(),
            'inActionJobs'        => $inActionJobs->get(),
            'passiveJobs'         => $passiveJobs->get(),
            'historyJobs'         => $historyJobs->get(),

            'unacceptedJobsCount' => $unacceptedJobs->count(),
            'activeJobsCount'     => $activeJobs->count(),
            'inActionJobsCount'   => $inActionJobs->count(),
            'passiveJobsCount'    => $passiveJobs->count(),
            'historyJobsCount'    => $historyJobs->count(),
        ]);
    }

    public function companyJobsGetById($id) {
        return response()->json([
            'status'  => 'success',
            'districts' => District::select('id', 'country_id', 'name->ru as name_d')->where('country_id', $country_id)->get()
        ]);
    }

    public function showList($id) {
        $companyId = (Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id);
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
        ->where('company_id', $companyId)
        ->withCount([
            'graphics AS total_hours' => function ($query) {
                $query->select(DB::raw("SUM((TIMESTAMPDIFF(MINUTE, started_at, finished_at) / 60) - lunch / 60)"));
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
        ->where('id', $id)
        ->first();

        $title = $job?->branche?->title_b ? ' / ' . $job?->branche?->title_b . ' #' . $job->id : '';

        return Inertia::render('Company/Jobs/Job', [
            'title' => __('general.jobs') . $title,
            'job'   => $job,
        ]);
    }

    public function companyJobsAdd(Request $request) {
        if(Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()))
            return redirect()->route('admin.dashboard');

        $job = Job::find($request->id)?->load('graphics', 'specialEmployees');

        $isAdministrator = Auth::user()->role == 'ADMINISTRATOR' ? true : false;
        $onlySupportedUsers = Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;
        $onlySupportedCompanies = Auth::user()->role == 'ADMINISTRATOR' && in_array(8, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;

        $jobSpecialBrancheIDS = [];
        $jobSpecialEmployeeIDS = [];
        $advantageEmployeeIDS = [];
        $selectedEmployeeOptions = [];
        $selectedBrancheOptions = [];

        if($job) {
            if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
                $brancheIds = Misc::getModeratorBranches();
                if(!in_array($job->branche_id, $brancheIds->toArray())) return redirect('/');
            }

            $jobSpecialBrancheIDS    = JobSpecialBranche::where('job_id', $job->id)->groupBy('branche_id')->pluck('branche_id')->toArray();
            $jobSpecialEmployeeIDS   = JobSpecialEmployee::where('job_id', $job->id)->groupBy('user_id')->pluck('user_id')->toArray();
            $selectedEmployeeOptions = User::select('id', 'name')->whereIn('id', $jobSpecialEmployeeIDS)->get();
            $selectedBrancheOptions  = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->whereIn('id', $jobSpecialBrancheIDS)->get();
            $advantageEmployeeIDS    = JobAdvantageEmployee::select('advantage_employee_id')->where('job_id', $job->id)->pluck('advantage_employee_id')->toArray();
        }

        if(Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') {
            $fileRenderName = 'AdminAdd';

            $branches = [];
            $establishments = [];

            if($job) {
                $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')
                    ->when($job?->company_id, function ($q) use ($job) {
                        return $q->where(function ($q) use ($job) {
                            $q->where('user_id', $job->company_id);
                        });
                    })->get();

                $establishments = Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->when($job?->company_id, function ($q) use ($job) {
                    return $q->where(function ($q) use ($job) {
                        $q->where('user_id', $job->company_id);
                    });
                })->where('status', 'CONFIRMED')->get();
            }


            if($isAdministrator) {
                $companies = User::select('id', 'company_name')->where('role', 'COMPANY')
                    ->when($isAdministrator, function ($q) use ($onlySupportedCompanies) {
                        return $q->where(function ($q) use ($onlySupportedCompanies) {
                            if($onlySupportedCompanies) {
                                $q->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            }
                        });
                    })->get();
            } else {
                $companies = User::select('id', 'company_name')->where('role', 'COMPANY')->get();
            }

            $companyAdvantageEmployeeIds = AdvantageEmploye::select('id', 'name->'. app()->getLocale(). ' as name_a')
                                                ->whereHas('userAdvantageEmployees', function (Builder $query) use($job) {
                                                    $query->where('user_id', $job?->company_id);
                                                })->pluck('id');
        } else {
            $fileRenderName = 'Add';
            $companyId = (Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id);
            $establishments = Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->where('company_id', $companyId)->where('status', 'CONFIRMED')->get();
            $companies = [];
            $companyAdvantageEmployeeIds = User::find($companyId)->userAdvantageEmployees->pluck('id');

            if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
                $brancheIds = Misc::getModeratorBranches();
                $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->whereIn('id', $brancheIds)->get();
            } else {
                $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $companyId)->get();
            }
        }

        $advantageEmployees = AdvantageEmploye::select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon')->whereIn('id', $companyAdvantageEmployeeIds)->get();

        return Inertia::render('Company/Jobs/'  . $fileRenderName, [
            'title'                   => __('general.jobs'),
            'data'                    => $job,
            'branches'                => $branches,
            'establishments'          => $establishments,
            'currencies'              => Currency::select('id', 'name')->get(),
            'jobTypes'                => JobType::select('id', 'name->'. app()->getLocale(). ' as name_t')->get(),
            'jobSpecialBrancheIDS'    => $jobSpecialBrancheIDS,
            'jobSpecialEmployeeIDS'   => $jobSpecialEmployeeIDS,
            'advantageEmployeeIDS'    => $advantageEmployeeIDS,
            'selectedEmployeeOptions' => $selectedEmployeeOptions,
            'selectedBrancheOptions'  => $selectedBrancheOptions,
            'companies'               => $companies,
            'advantageEmployees'      => $advantageEmployees,
        ]);
    }

    public function addCompanyJob(AddCompanyJobRequest $request) {
        try {
            if(Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()))
                return response()->json(['message' => __('general.not_added')], 500);

            if(Auth::user()->role == 'COMPANY' && !Auth::user()->company_name)
                return response()->json(['message' => __('general.not_added')], 500);

            $checkGraphics = $this->checkGraphics($request->graphics);
            if(!$checkGraphics)
                return response()->json(['message' => __('messages.range_14_days')], 500);

            $checkSameTimeGraphics = $this->checkSameTimeGraphics($request->graphics);
            if(!$checkSameTimeGraphics)
                return response()->json(['message' => __('messages.sameTimeGraphicError')], 500);

            if($request->is_active) {
                $checkTimeGraphics = $this->checkTimeGraphics($request->graphics);
                if(!$checkTimeGraphics)
                    return response()->json(['message' => __('messages.checkVacancyRetroactively')], 500);
            }

            $job = Job::create([
                'job_type_id'        => $request->job_type_id,
                'company_id'         => (Auth::user()->role == 'COMPANY' ? Auth::user()->id : (Auth::user()->parent_user_id ? Auth::user()->parent_user_id : $request->company_id)),
                'user_id'            => Auth::user()->id,
                'establishment_id'   => $request->establishment_id,
                'branche_id'         => $request->branche_id,
                'currency_id'        => $request->currency_id,
                'employee_qnt'       => $request->employee_qnt,
                'bonus'              => $request->bonus,
                'is_need_internship' => $request->is_need_internship,
                'is_paid_lunch'      => $request->is_paid_lunch,
                'is_active'          => $request->is_active,
            ]);

            if($request->specialEmployees) {
                foreach($request->specialEmployees as $specialEmployee) {
                    JobSpecialEmployee::create([
                        'job_id' => $job->id,
                        'user_id' => $specialEmployee
                    ]);
                }
            }

            if($request->specialBranches) {
                foreach($request->specialBranches as $specialBranche) {
                    JobSpecialBranche::create([
                        'job_id'     => $job->id,
                        'branche_id' => $specialBranche
                    ]);
                }
            }

            if($request->advantageEmployees) {
                foreach($request->advantageEmployees as $advantageEmployee) {
                    JobAdvantageEmployee::create([
                        'job_id'                => $job->id,
                        'advantage_employee_id' => $advantageEmployee
                    ]);
                }
            }

            $job->graphics()->createMany($request->graphics);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function updateCompanyJob(AddCompanyJobRequest $request) {
        try {
            if(Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()))
                return response()->json(['message' => __('general.not_added')], 500);

            $checkGraphics = $this->checkGraphics($request->graphics);
            if(!$checkGraphics)
                return response()->json(['message' => __('messages.range_14_days')], 500);

            $checkSameTimeGraphics = $this->checkSameTimeGraphics($request->graphics);
            if(!$checkSameTimeGraphics)
                return response()->json(['message' => __('messages.sameTimeGraphicError')], 500);

            if($request->is_active) {
                $checkTimeGraphics = $this->checkTimeGraphics($request->graphics);
                if(!$checkTimeGraphics)
                    return response()->json(['message' => __('messages.checkVacancyRetroactively')], 500);
            }


            if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
                $brancheIds = Misc::getModeratorBranches();
                if(!in_array($request->branche_id, $brancheIds->toArray())) return redirect('/');
            }

            $job = tap(Job::where('id', $request->id))->update([
                'job_type_id'        => $request->job_type_id,
                'establishment_id'   => $request->establishment_id,
                'branche_id'         => $request->branche_id,
                'currency_id'        => $request->currency_id,
                'employee_qnt'       => $request->employee_qnt,
                'bonus'              => $request->bonus,
                'is_need_internship' => $request->is_need_internship,
                'is_paid_lunch'      => $request->is_paid_lunch,
                'is_active'          => $request->is_active,
            ])->first();

            $job->graphics()->delete();
            $job->graphics()->createMany(collect($request->graphics)->sortBy('finished_at'));


            $job->specialEmployees()->delete();
            if($request->specialEmployees) {
                foreach($request->specialEmployees as $specialEmployee) {
                    JobSpecialEmployee::create([
                        'job_id' => $job->id,
                        'user_id' => $specialEmployee
                    ]);
                }
            }

            $job->specialBranches()->delete();
            if($request->specialBranches) {
                foreach($request->specialBranches as $specialBranche) {
                    JobSpecialBranche::create([
                        'job_id'     => $job->id,
                        'branche_id' => $specialBranche
                    ]);
                }
            }

            $job->advantageEmployees()->delete();
            if($request->advantageEmployees) {
                foreach($request->advantageEmployees as $advantageEmployee) {
                    JobAdvantageEmployee::create([
                        'job_id'                => $job->id,
                        'advantage_employee_id' => $advantageEmployee
                    ]);
                }
            }
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_edited')], 500);
        }

        return response()->json(['message' => __('general.successfully_edited')], 201);
    }

    public function deleteCompanyJob(Request $request) {
        if(Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()))
            return redirect()->route('admin.dashboard');

        $job = Job::find($request->id);

        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $brancheIds = Misc::getModeratorBranches();
            if(!in_array($job->branche_id, $brancheIds->toArray())) return redirect('/');
        }

        return $job->delete()
            ? redirect()->route('company_jobs')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('company_jobs')->with('errorMessage', __('general.not_deleted'));
    }

    // public function addCompanyJobGraphic(AddCompanyJobGraphicRequest $request) {
    //     try {
    //         $job = Job::select('company_id', 'branche_id')->where('id', $request->job_id)->first();

    //         if($job) {
    //             JobTimeGraphic::create([
    //                 'job_id'        => $request->job_id,
    //                 'started_at'    => $request->started_at,
    //                 'finished_at'   => $request->finished_at,
    //                 'lunch'         => $request->lunch,
    //                 'is_late_added' => $request->is_late_added,
    //             ]);

    //             // foreach($request->employeeIds as $id) {
    //             //     JobApplication::create([
    //             //         'user_id'    => $id,
    //             //         'job_id'     => $request->job_id,
    //             //         'company_id' => $job->company_id,
    //             //         'branche_id' => $job->branche_id,
    //             //     ]);
    //             // }
    //         }
    //     } catch(\Exception $e) {
    //         Log::info($e);
    //         return response()->json(['message' => __('general.not_added')], 500);
    //     }

    //     return response()->json(['message' => __('general.successfully_added')], 201);
    // }

    public function updateCompanyJobGraphic(AddCompanyJobRequest $request) {
        try {

        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_edited')], 500);
        }

        return response()->json(['message' => __('general.successfully_edited')], 201);
    }

    public function deleteCompanyJobGraphic() {

    }

    public function checkGraphics($graphics) {
        if($graphics) {
            $dates = [];

            foreach($graphics as $graphic) {
                $dates[] = date('Y-m-d', strtotime($graphic['started_at']));
            }

            $startDate = min($dates);
            $endDate   = max($dates);

            if($startDate && $endDate) {
                $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
                if($days > 13) return false;
            }
        }

        return true;
    }

    public function checkSameTimeGraphics($graphics) {
        if($graphics && count($graphics) > 1) {
            foreach($graphics as $graphic_1) {
                foreach($graphics as $graphic_2) {
                    if(
                        date('Y-m-d', strtotime($graphic_1['started_at'])) == date('Y-m-d', strtotime($graphic_2['started_at'])) &&
                        $graphic_1['id'] != $graphic_2['id']
                    ) {
                        $start_1 = strtotime($graphic_1['started_at']);
                        $end_1 = strtotime($graphic_1['finished_at']);

                        $start_2 = strtotime($graphic_2['started_at']);
                        $end_2 = strtotime($graphic_2['finished_at']);

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

        return true;
    }

    public function checkTimeGraphics($graphics) {
        if($graphics && count($graphics) > 1) {
            foreach($graphics as $graphic) {
                if(
                    date("Y-m-d", strtotime($graphic['started_at'])) < date("Y-m-d") || date("Y-m-d", strtotime($graphic['finished_at'])) < date("Y-m-d")
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getWorkerLists($jobID) {
        $userIds = JobApplication::where('job_id', $jobID)->groupBy('user_id')->pluck('user_id')->toArray();

        return response()->json([
            'status' => 'success',
            'data'   => JobApplication::where('job_id', $jobID)->with([
                'user' => function($q) {
                    $q->select('id',  'name', 'phone');
                }
            ])->get()
        ]);
    }

    public function getJobType($jobID) {
        $job = Job::select('job_type_id')->where('id', $jobID)->groupBy('user_id')->first();

        $data = [];
        $type = '';
        if($job && $job->job_type_id) {
            if($job->job_type_id == 3 || $job->job_type_id == 4) {
                $type = 'branche';
                $data = JobSpecialBranche::where('job_id', $jobID)->with([
                    'branche' => function($q) {
                        $q->select('id',  'title->'. app()->getLocale(). ' as title_b');
                    }
                ])->get();
            } else if($job->job_type_id == 5) {
                $type = 'employee';
                $data = JobSpecialEmployee::where('job_id', $jobID)->with([
                    'user' => function($q) {
                        $q->select('id',  'name', 'email', 'phone');
                    }
                ])->get();
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data,
            'type'   => $type
        ]);
    }

    public function getAddShiftJobs() {
        $startDate = date('Y-m-d', strtotime(date('Y-m-d'). ' - 3 days')) . ' 00:00:00';
        $finishDate = date('Y-m-d', strtotime(date('Y-m-d'). ' + 3 days')) . ' 23:59:59';
        $companyId = (Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id);

        $jobs = Job::select(
            'jobs.id',
            'jobs.establishment_id',
            'establishments.name->ru as establishment_name',
            DB::raw("CONCAT(establishments.name, ' - ', jobs.id) as full_name")
        )->with([
            'applications' => function($query) {
                $query->with([
                    'user' => function($query) {
                        $query->select('id', 'email', 'name', 'company_name', 'company_phone');
                    },
                ])->groupBy('job_applications.user_id')->groupBy('job_applications.job_id');
            },
        ])->join('establishments', function($join) {
            $join->on('establishments.id', '=', 'jobs.establishment_id')
                  ->whereNull('establishments.deleted_at');
        })
        ->has('applications')
        ->where(function($q) use($startDate, $finishDate) {
            $q->where(function($q) use($startDate, $finishDate) {
                // $q->whereHas('firstGraphic',function($q) {
                //     $q->whereDate('started_at', '<=', date('Y-m-d', strtotime(date('Y-m-d'). ' + 3 days')));
                // })
                $q->whereHas('lastGraphic',function($q) use($startDate, $finishDate) {
                    // dd($startDate, $finishDate);
                    $q->whereBetween('finished_at', [$startDate, $finishDate]);
                    // $q->whereDate('finished_at', '<=', date('Y-m-d', strtotime(date('Y-m-d'). ' - 3 days')))
                    //   ->whereDate('finished_at', '>=', date('Y-m-d', strtotime(date('Y-m-d'). ' + 3 days')));
                });
            });
        })
        ->where('is_active', 1);

        if(Auth::user()->role == 'COMPANY' || Auth::user()->role == 'MODERATOR') {
            $jobs = $jobs->where('jobs.company_id', $companyId);
        }

        $jobs = $jobs->get();

        return response()->json([
            'status' => 'success',
            'jobs'   => $jobs
        ]);
    }
}
