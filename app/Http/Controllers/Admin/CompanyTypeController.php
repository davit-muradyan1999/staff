<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminCompanyTypeRequest;
use App\Models\CompanyType;

class CompanyTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/CompanyTypes/List', [
            'title'        => __('general.companyType'),
            'companyTypes' => CompanyType::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/CompanyTypes/Add', [
            'title' => __('general.companyType') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminCompanyTypeRequest $request)
    {
        $companyType       = new CompanyType();
        $companyType->name = $request->name;

        return $companyType->save()
            ? redirect()->route('admin.company_types.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.company_types.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(CompanyType $companyType)
    {
        return Inertia::render('Admin/Libraries/CompanyTypes/Add', [
            'title' => __('general.companyType') . ' / ' . __('general.edit'),
            'data'  => $companyType,
        ]);
    }

    public function update(AdminCompanyTypeRequest $request, CompanyType $companyType)
    {
        $companyType->name = $request->name;

        return $companyType->save()
            ? redirect()->route('admin.company_types.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.company_types.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(CompanyType $companyType)
    {
        return $companyType->delete()
            ? redirect()->route('admin.company_types.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.company_types.index')->with('errorMessage', __('general.not_deleted'));
    }
}
