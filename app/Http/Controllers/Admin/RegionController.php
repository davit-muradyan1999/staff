<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminRegionRequest;
use App\Models\Region,
    App\Models\District,
    App\Models\Country;

class RegionController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Regions/List', [
            'title'     => __('general.regions'),
            'regions' => Region::with([
                'district' => function($query) {
                    $query->select('id', 'country_id', 'name->ru as name_d')->with([
                        'country' => function($query) {
                            $query->select('id','name->ru as name_c');
                        }
                    ]);
                }
            ])->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Regions/Add', [
            'title'     => __('general.regions') . ' / ' . __('general.add'),
            'countries' => Country::select('id', 'name->ru as name_c')->get(),
        ]);
    }

    public function store(AdminRegionRequest $request)
    {
        $region              = new Region();
        $region->district_id = $request->district_id;
        $region->name        = $request->name;

        return $region->save()
            ? redirect()->route('admin.regions.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.regions.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Region $region)
    {
        return Inertia::render('Admin/Libraries/Regions/Add', [
            'title'     => __('general.federalDistrict') . ' / ' . __('general.edit'),
            'countries' => Country::select('id', 'name->ru as name_c')->get(),
            'districts' => District::select('id', 'country_id', 'name->ru as name_d')->where('country_id', $region?->district?->country_id)->get(),
            'data'      => $region->load('district.country'),
        ]);
    }

    public function update(AdminRegionRequest $request, Region $region)
    {
        $region->district_id = $request->district_id;
        $region->name       = $request->name;

        return $region->save()
            ? redirect()->route('admin.regions.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.regions.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Region $region)
    {
        return $region->delete()
            ? redirect()->route('admin.regions.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.regions.index')->with('errorMessage', __('general.not_deleted'));
    }

    public function getRegionsByDistrict($district_id)
    {
        return response()->json([
            'status'  => 'success',
            'regions' => Region::select('id', 'district_id', 'name->ru as name_r')->where('district_id', $district_id)->get()
        ]);
    }
}
