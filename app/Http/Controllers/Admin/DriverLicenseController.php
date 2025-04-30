<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminDriverLicenseRequest;
use App\Models\DriverLicense;

class DriverLicenseController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/DriverLicenses/List', [
            'title'          => __('general.driverLicense'),
            'driverLicenses' => DriverLicense::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/DriverLicenses/Add', [
            'title' => __('general.driverLicense') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminDriverLicenseRequest $request)
    {
        $driverLicense       = new DriverLicense();
        $driverLicense->name = $request->name;

        return $driverLicense->save()
            ? redirect()->route('admin.driver_licenses.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.driver_licenses.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(DriverLicense $driverLicense)
    {
        return Inertia::render('Admin/Libraries/DriverLicenses/Add', [
            'title' => __('general.driverLicense') . ' / ' . __('general.edit'),
            'data'  => $driverLicense,
        ]);
    }

    public function update(AdminDriverLicenseRequest $request, DriverLicense $driverLicense)
    {
        $driverLicense->name = $request->name;

        return $driverLicense->save()
            ? redirect()->route('admin.driver_licenses.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.driver_licenses.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(DriverLicense $driverLicense)
    {
        return $driverLicense->delete()
            ? redirect()->route('admin.driver_licenses.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.driver_licenses.index')->with('errorMessage', __('general.not_deleted'));
    }
}
