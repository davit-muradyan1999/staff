<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminCountryRequest;
use App\Models\Country;

class CountryController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Countries/List', [
            'title'     => __('general.countries'),
            'countries' => Country::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Countries/Add', [
            'title' => __('general.countries') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminCountryRequest $request)
    {
        $country       = new Country();
        $country->name = $request->name;

        return $country->save()
            ? redirect()->route('admin.countries.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.countries.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Country $country)
    {
        return Inertia::render('Admin/Libraries/Countries/Add', [
            'title' => __('general.countries') . ' / ' . __('general.edit'),
            'data'  => $country,
        ]);
    }

    public function update(AdminCountryRequest $request, Country $country)
    {
        $country->name = $request->name;

        return $country->save()
            ? redirect()->route('admin.countries.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.countries.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Country $country)
    {
        return $country->delete()
            ? redirect()->route('admin.countries.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.countries.index')->with('errorMessage', __('general.not_deleted'));
    }
}
