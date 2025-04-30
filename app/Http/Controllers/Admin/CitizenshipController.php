<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminCitizenshipRequest;
use App\Models\Citizenship;

class CitizenshipController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Citizenships/List', [
            'title'        => __('general.citizenship'),
            'citizenships' => Citizenship::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Citizenships/Add', [
            'title' => __('general.citizenship') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminCitizenshipRequest $request)
    {
        $citizenship       = new Citizenship();
        $citizenship->name = $request->name;

        return $citizenship->save()
            ? redirect()->route('admin.citizenships.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.citizenships.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Citizenship $citizenship)
    {
        return Inertia::render('Admin/Libraries/Citizenships/Add', [
            'title' => __('general.citizenship') . ' / ' . __('general.edit'),
            'data'  => $citizenship,
        ]);
    }

    public function update(AdminCitizenshipRequest $request, Citizenship $citizenship)
    {
        $citizenship->name = $request->name;

        return $citizenship->save()
            ? redirect()->route('admin.citizenships.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.citizenships.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Citizenship $citizenship)
    {
        return $citizenship->delete()
            ? redirect()->route('admin.citizenships.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.citizenships.index')->with('errorMessage', __('general.not_deleted'));
    }
}
