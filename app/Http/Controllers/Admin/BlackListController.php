<?php

namespace App\Http\Controllers\Admin;

use DB;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BlackList,
    App\Models\User;

class BlackListController extends Controller
{
    public function index(Request $request)
    {
        $isAdministrator = Auth::user()->role == 'ADMINISTRATOR' ? true : false;
        $onlySupportedUsers = Auth::user()->role == 'ADMINISTRATOR' && in_array(7, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;
        $onlySupportedCompanies = Auth::user()->role == 'ADMINISTRATOR' && in_array(8, Auth::user()->permissions->pluck('id')->toArray()) ? true : false;

        $employees = User::select([
            'id',
            'name',
            'profile_photo_path',
        ])->whereHas('blackListEmployee', function (Builder $query) {
            $query->where('status', 'ADDED');
        })->with([
            'blackListEmployee' => function($q) {
                $q->with([
                    'company' => function($q) {
                        $q->select('id', 'name');
                    },
                    'employee' => function($q) {
                        $q->select('id', 'name');
                    }
                ])->where('status', 'ADDED');
            }
        ])
        ->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->text . '%');
                });
            });
        })
        ->when($request->companies, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('blackListEmployee', function (Builder $query) use ($request) {
                    $query->whereIn('company_id', $request->companies)->where('status', 'ADDED');
                });
            });
        })
        ->when($isAdministrator, function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
            return $q->where(function ($q) use ($onlySupportedUsers, $onlySupportedCompanies) {
                if($onlySupportedUsers) {
                    $q->whereHas('userSupports', function (Builder $query) {
                        $query->where('support_user_id', Auth::user()->id);
                    });
                }

                if($onlySupportedCompanies) {
                    if($onlySupportedUsers) {
                        $q->orWhereHas('applications', function (Builder $query) {
                            $query->whereHas('company', function (Builder $query) {
                                $query->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        });
                    } else {
                        $q->whereHas('applications', function (Builder $query) {
                            $query->whereHas('company', function (Builder $query) {
                                $query->whereHas('userSupports', function (Builder $query) {
                                    $query->where('support_user_id', Auth::user()->id);
                                });
                            });
                        });
                    }
                }
            });
        })
        ->paginate(50)
        ->appends(request()->query());

        $companies = User::select('id', 'company_name')->where('role', 'COMPANY')->when($onlySupportedCompanies, function ($q) {
            return $q->where(function ($q) {
                $q->whereHas('userSupports', function (Builder $query) {
                    $query->where('support_user_id', Auth::user()->id);
                });
            });
        })->get();

        return Inertia::render('Admin/Libraries/BlackLists/List', [
            'title'     => __('general.blackList'),
            'employees' => $employees,
            'companies' => $companies,
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
