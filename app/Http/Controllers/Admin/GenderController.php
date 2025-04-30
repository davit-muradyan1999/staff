<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminGenderRequest;
use App\Models\Gender;

class GenderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Genders/List', [
            'title'   => __('general.gender'),
            'genders' => Gender::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Genders/Add', [
            'title' => __('general.gender') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminGenderRequest $request)
    {
        $gender       = new Gender();
        $gender->name = $request->name;

        return $gender->save()
            ? redirect()->route('admin.genders.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.genders.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Gender $gender)
    {
        return Inertia::render('Admin/Libraries/Genders/Add', [
            'title' => __('general.gender') . ' / ' . __('general.edit'),
            'data'  => $gender,
        ]);
    }

    public function update(AdminGenderRequest $request, Gender $gender)
    {
        $gender->name = $request->name;

        return $gender->save()
            ? redirect()->route('admin.genders.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.genders.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Gender $gender)
    {
        return $gender->delete()
            ? redirect()->route('admin.genders.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.genders.index')->with('errorMessage', __('general.not_deleted'));
    }
}
