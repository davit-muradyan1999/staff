<?php

namespace App\Http\Controllers\Admin;

use DB;
use Log;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User,
    App\Models\CompanyBalance,
    App\Models\CompanyBalanceList;

class CompanyBalanceController extends Controller
{
    public function companiesBalance(Request $request)
    {
        $companies = User::where('role', 'COMPANY')->whereNotNull('email_verified_at')->get();

        $totalCompaniesBalance = CompanyBalance::sum('balance');

        $companiesBalance = CompanyBalance::with([
            'company' => function($query) {
                $query->select('id', 'email', 'name', 'company_name');
            }
        ])

        ->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('company', function (Builder $query) use ($request) {
                    $query->where('company_name', 'like', '%' . $request->text . '%');
                });
            });
        })->when($request->companies, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereIn('company_id', $request->companies);
            });
        })->get();

        return Inertia::render('Admin/Libraries/CompanyBalance/List', [
            'title'                 => __('general.companyBalances'),
            'companies'             => $companies,
            'companiesBalance'      => $companiesBalance,
            'totalCompaniesBalance' => $totalCompaniesBalance,
        ]);
    }
}
