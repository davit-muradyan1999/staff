<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminIndustryRequest;
use App\Models\Industry;

class IndustryController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Industries/List', [
            'title'      => __('general.industry'),
            'industries' => Industry::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Industries/Add', [
            'title' => __('general.industry') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminIndustryRequest $request)
    {
        $industry       = new Industry();
        $industry->name = $request->name;

        return $industry->save()
            ? redirect()->route('admin.industries.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.industries.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Industry $industry)
    {
        return Inertia::render('Admin/Libraries/Industries/Add', [
            'title' => __('general.industry') . ' / ' . __('general.edit'),
            'data'  => $industry,
        ]);
    }

    public function update(AdminIndustryRequest $request, Industry $industry)
    {
        $industry->name = $request->name;

        return $industry->save()
            ? redirect()->route('admin.industries.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.industries.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Industry $industry)
    {
        return $industry->delete()
            ? redirect()->route('admin.industries.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.industries.index')->with('errorMessage', __('general.not_deleted'));
    }
}
