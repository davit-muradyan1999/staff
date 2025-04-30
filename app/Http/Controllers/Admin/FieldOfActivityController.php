<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminFieldOfActivityRequest;
use App\Models\FieldOfActivity;

class FieldOfActivityController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/FieldOfActivities/List', [
            'title'             => __('general.fieldOfActivity'),
            'fieldOfActivities' => FieldOfActivity::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/FieldOfActivities/Add', [
            'title' => __('general.fieldOfActivity') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminFieldOfActivityRequest $request)
    {
        $fieldOfActivity       = new FieldOfActivity();
        $fieldOfActivity->name = $request->name;

        return $fieldOfActivity->save()
            ? redirect()->route('admin.field_of_activities.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.field_of_activities.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(FieldOfActivity $fieldOfActivity)
    {
        return Inertia::render('Admin/Libraries/FieldOfActivities/Add', [
            'title' => __('general.fieldOfActivity') . ' / ' . __('general.edit'),
            'data'  => $fieldOfActivity,
        ]);
    }

    public function update(AdminFieldOfActivityRequest $request, FieldOfActivity $fieldOfActivity)
    {
        $fieldOfActivity->name = $request->name;

        return $fieldOfActivity->save()
            ? redirect()->route('admin.field_of_activities.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.field_of_activities.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(FieldOfActivity $fieldOfActivity)
    {
        return $fieldOfActivity->delete()
            ? redirect()->route('admin.field_of_activities.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.field_of_activities.index')->with('errorMessage', __('general.not_deleted'));
    }
}
