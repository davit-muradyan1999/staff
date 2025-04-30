<?php

namespace App\Http\Controllers\Admin;

use DB;
use Auth;
use Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Job;

class JobController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Jobs/List', [
            'title' => __('general.jobs'),
            'jobs' => Job::with([
                'establishment' => function($query) { $query->select('id', 'name->ru as name_e'); },
                'branche'       => function($query) { $query->select('id', 'title->ru as title_b'); },
                'currency'      => function($query) { $query->select('id', 'name'); },
                'graphics'
            ])
            ->paginate(50)
            ->appends(request()->query()),
        ]);
    }

    public function showJobList($id)
    {
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
        ->where('id', $id)->first();

        $title = $job?->branche?->title_b ? ' / ' . $job?->branche?->title_b . ' #' . $job->id : '';

        return Inertia::render('Admin/Libraries/Jobs/Job', [
            'title' => __('general.jobs') . $title,
            'job'   => $job,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
