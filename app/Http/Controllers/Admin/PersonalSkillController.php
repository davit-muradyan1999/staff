<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminPersonalSkillRequest;
use App\Models\PersonalSkill;

class PersonalSkillController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/PersonalSkills/List', [
            'title'     => __('general.personalSkills'),
            'personalSkills' => PersonalSkill::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/PersonalSkills/Add', [
            'title' => __('general.personalSkills') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminPersonalSkillRequest $request)
    {
        $personalSkill       = new PersonalSkill();
        $personalSkill->name = $request->name;

        return $personalSkill->save()
            ? redirect()->route('admin.personal_skills.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.personal_skills.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(PersonalSkill $personalSkill)
    {
        return Inertia::render('Admin/Libraries/PersonalSkills/Add', [
            'title' => __('general.personalSkills') . ' / ' . __('general.edit'),
            'data'  => $personalSkill,
        ]);
    }

    public function update(AdminPersonalSkillRequest $request, PersonalSkill $personalSkill)
    {
        $personalSkill->name = $request->name;

        return $personalSkill->save()
            ? redirect()->route('admin.personal_skills.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.personal_skills.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(PersonalSkill $personalSkill)
    {
        return $personalSkill->delete()
            ? redirect()->route('admin.personal_skills.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.personal_skills.index')->with('errorMessage', __('general.not_deleted'));
    }
}
