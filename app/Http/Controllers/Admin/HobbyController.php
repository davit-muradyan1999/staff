<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminHobbyRequest;
use App\Models\Hobby;

class HobbyController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Hobbies/List', [
            'title'   => __('general.hobbyAndInterest'),
            'hobbies' => Hobby::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Hobbies/Add', [
            'title' => __('general.hobbyAndInterest') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminHobbyRequest $request)
    {
        $hobby       = new Hobby();
        $hobby->name = $request->name;

        return $hobby->save()
            ? redirect()->route('admin.hobbies.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.hobbies.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Hobby $hobby)
    {
        return Inertia::render('Admin/Libraries/Hobbies/Add', [
            'title' => __('general.hobbyAndInterest') . ' / ' . __('general.edit'),
            'data'  => $hobby,
        ]);
    }

    public function update(AdminHobbyRequest $request, Hobby $hobby)
    {
        $hobby->name = $request->name;

        return $hobby->save()
            ? redirect()->route('admin.hobbies.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.hobbies.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Hobby $hobby)
    {
        return $hobby->delete()
            ? redirect()->route('admin.hobbies.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.hobbies.index')->with('errorMessage', __('general.not_deleted'));
    }
}
