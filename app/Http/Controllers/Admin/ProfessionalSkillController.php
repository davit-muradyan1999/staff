<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminProfessionalSkillRequest;
use App\Models\ProfessionalSkill;

class ProfessionalSkillController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/ProfessionalSkills/List', [
            'title'     => __('general.professionalSkills'),
            'professionalSkills' => ProfessionalSkill::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/ProfessionalSkills/Add', [
            'title' => __('general.professionalSkills') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminProfessionalSkillRequest $request)
    {
        $professionalSkill       = new ProfessionalSkill();
        $professionalSkill->name = $request->name;

        return $professionalSkill->save()
            ? redirect()->route('admin.professional_skills.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.professional_skills.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(ProfessionalSkill $professionalSkill)
    {
        return Inertia::render('Admin/Libraries/ProfessionalSkills/Add', [
            'title' => __('general.professionalSkills') . ' / ' . __('general.edit'),
            'data'  => $professionalSkill,
        ]);
    }

    public function update(AdminProfessionalSkillRequest $request, ProfessionalSkill $professionalSkill)
    {
        $professionalSkill->name = $request->name;

        return $professionalSkill->save()
            ? redirect()->route('admin.professional_skills.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.professional_skills.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(ProfessionalSkill $professionalSkill)
    {
        return $professionalSkill->delete()
            ? redirect()->route('admin.professional_skills.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.professional_skills.index')->with('errorMessage', __('general.not_deleted'));
    }
}
