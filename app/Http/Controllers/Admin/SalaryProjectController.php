<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Classes\SalaryProjectClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryProjectController extends Controller
{
    public function salaryProjectResults(Request $request)
    {
        $startedAt = $request->startedAt ? $request->startedAt : date('Y-m-01');
        $finishedAt = $request->finishedAt ? $request->finishedAt : date('Y-m-d');

        $data = match ($request->type) {
            'SALARY_GET_PAYMENT_REGISTRY_LIST' => (new SalaryProjectClass())->salaryGetPaymentRegistryList($startedAt, $finishedAt),
            default => [],
        };

        return Inertia::render('Admin/Libraries/SalaryProjects/Result', [
            'title'      => __('general.salaryProject'),
            'data'       => $data,
            'startedAt'  => $startedAt,
            'finishedAt' => $finishedAt,
        ]);
    }
}
