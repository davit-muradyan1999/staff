<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminPermissionRequest;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Permissions/List', [
            'title'       => __('general.roles'),
            'permissions' => Permission::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Permissions/Add', [
            'title' => __('general.roles') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminPermissionRequest $request)
    {
        $permission       = new Permission();
        $permission->type = $request->type;
        $permission->name = $request->name;

        return $permission->save()
            ? redirect()->route('admin.permissions.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.permissions.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Permission $permission)
    {
        return Inertia::render('Admin/Libraries/Permissions/Add', [
            'title' => __('general.permissions') . ' / ' . __('general.edit'),
            'data'  => $permission,
        ]);
    }

    public function update(AdminPermissionRequest $request, Permission $permission)
    {
        $permission->type = $request->type;
        $permission->name = $request->name;

        return $permission->save()
            ? redirect()->route('admin.permissions.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.permissions.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Permission $permission)
    {
        return $permission->delete()
            ? redirect()->route('admin.language_skills.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.permissions.index')->with('errorMessage', __('general.not_deleted'));
    }
}
