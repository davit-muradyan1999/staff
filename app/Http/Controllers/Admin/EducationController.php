<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminEducationRequest;
use App\Models\Education;

class EducationController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Education/List', [
            'title'     => __('general.educations'),
            'educations' => Education::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Education/Add', [
            'title' => __('general.educations') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminEducationRequest $request)
    {
        $education       = new Education();
        $education->name = $request->name;

        return $education->save()
            ? redirect()->route('admin.educations.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.educations.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Education $education)
    {
        return Inertia::render('Admin/Libraries/Education/Add', [
            'title' => __('general.educations') . ' / ' . __('general.edit'),
            'data'  => $education,
        ]);
    }

    public function update(AdminEducationRequest $request, Education $education)
    {
        $education->name = $request->name;

        return $education->save()
            ? redirect()->route('admin.educations.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.educations.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Education $education)
    {
        return $education->delete()
            ? redirect()->route('admin.educations.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.educations.index')->with('errorMessage', __('general.not_deleted'));
    }
}
