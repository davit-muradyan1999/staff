<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminDistrictRequest;
use App\Models\District,
    App\Models\Country;

class DistrictController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Districts/List', [
            'title'     => __('general.federalDistrict'),
            'districts' => District::with([
                'country' => function($query) {
                    $query->select('id', 'name');
                }
            ])->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Districts/Add', [
            'title'     => __('general.federalDistrict') . ' / ' . __('general.add'),
            'countries' => Country::select('id', 'name->ru as name_c')->get(),
        ]);
    }

    public function store(AdminDistrictRequest $request)
    {
        $district             = new District();
        $district->country_id = $request->country_id;
        $district->name       = $request->name;

        return $district->save()
            ? redirect()->route('admin.districts.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.districts.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(District $district)
    {
        return Inertia::render('Admin/Libraries/Districts/Add', [
            'title'     => __('general.federalDistrict') . ' / ' . __('general.edit'),
            'countries' => Country::select('id', 'name->ru as name')->get(),
            'data'      => $district,
        ]);
    }

    public function update(AdminDistrictRequest $request, District $district)
    {
        $district->country_id = $request->country_id;
        $district->name       = $request->name;

        return $district->save()
            ? redirect()->route('admin.districts.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.districts.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(District $district)
    {
        return $district->delete()
            ? redirect()->route('admin.districts.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.districts.index')->with('errorMessage', __('general.not_deleted'));
    }

    public function getDistrictsByCountry($country_id)
    {
        return response()->json([
            'status'  => 'success',
            'districts' => District::select('id', 'country_id', 'name->ru as name_d')->where('country_id', $country_id)->get()
        ]);
    }
}
