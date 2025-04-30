<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminLanguageSkillRequest;
use App\Models\LanguageSkill;

class LanguageSkillController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/LanguageSkills/List', [
            'title'     => __('general.languageSkills'),
            'languageSkills' => LanguageSkill::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/LanguageSkills/Add', [
            'title' => __('general.languageSkills') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminLanguageSkillRequest $request)
    {
        $languageSkill       = new LanguageSkill();
        $languageSkill->name = $request->name;

        return $languageSkill->save()
            ? redirect()->route('admin.language_skills.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.language_skills.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(LanguageSkill $languageSkill)
    {
        return Inertia::render('Admin/Libraries/LanguageSkills/Add', [
            'title' => __('general.languageSkills') . ' / ' . __('general.edit'),
            'data'  => $languageSkill,
        ]);
    }

    public function update(AdminLanguageSkillRequest $request, LanguageSkill $languageSkill)
    {
        $languageSkill->name = $request->name;

        return $languageSkill->save()
            ? redirect()->route('admin.language_skills.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.language_skills.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(LanguageSkill $languageSkill)
    {
        return $languageSkill->delete()
            ? redirect()->route('admin.language_skills.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.language_skills.index')->with('errorMessage', __('general.not_deleted'));
    }
}
