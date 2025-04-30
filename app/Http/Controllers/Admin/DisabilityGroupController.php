<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminDisabilityGroupRequest;
use App\Models\DisabilityGroup;

class DisabilityGroupController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/DisabilityGroups/List', [
            'title'            => __('general.disabilityGroups'),
            'disabilityGroups' => DisabilityGroup::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/DisabilityGroups/Add', [
            'title' => __('general.disabilityGroups') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminDisabilityGroupRequest $request)
    {
        $disabilityGroup       = new DisabilityGroup();
        $disabilityGroup->name = $request->name;

        return $disabilityGroup->save()
            ? redirect()->route('admin.disability_groups.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.disability_groups.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(DisabilityGroup $disabilityGroup)
    {
        return Inertia::render('Admin/Libraries/DisabilityGroups/Add', [
            'title' => __('general.disabilityGroups') . ' / ' . __('general.edit'),
            'data'  => $disabilityGroup,
        ]);
    }

    public function update(AdminDisabilityGroupRequest $request, DisabilityGroup $disabilityGroup)
    {
        $disabilityGroup->name = $request->name;

        return $disabilityGroup->save()
            ? redirect()->route('admin.disability_groups.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.disability_groups.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(DisabilityGroup $disabilityGroup)
    {
        return $disabilityGroup->delete()
            ? redirect()->route('admin.disability_groups.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.disability_groups.index')->with('errorMessage', __('general.not_deleted'));
    }
}
