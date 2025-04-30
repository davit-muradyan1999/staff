<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminCityRequest;
use App\Models\City,
    App\Models\Region,
    App\Models\District,
    App\Models\TerritoryType,
    App\Models\Country;

class CityController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Cities/List', [
            'title'     => __('general.settlements'),
            'cities' => City::with([
                'region' => function($query) {
                    $query->select('id', 'district_id', 'name->ru as name_r')->with([
                        'district' => function($query) {
                            $query->select('id', 'country_id', 'name->ru as name_d')->with([
                                'country' => function($query) {
                                    $query->select('id', 'name->ru as name_c');
                                }
                            ]);
                        }
                    ]);
                },
                'territory_type' => function($query) {
                    $query->select('id', 'name->ru as name_t');
                }
            ])->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Cities/Add', [
            'title'          => __('general.settlements') . ' / ' . __('general.add'),
            'territoryTypes' => TerritoryType::select('id', 'name->ru as name_t')->get(),
            'countries'      => Country::select('id', 'name->ru as name_c')->get(),
            'districts'      => [],
            'regions'        => [],
        ]);
    }

    public function store(AdminCityRequest $request)
    {
        $city                    = new City();
        $city->territory_type_id = $request->territory_type_id;
        $city->region_id         = $request->region_id;
        $city->name              = $request->name;

        return $city->save()
            ? redirect()->route('admin.cities.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.cities.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(City $city)
    {
        return Inertia::render('Admin/Libraries/Cities/Add', [
            'title'          => __('general.settlements') . ' / ' . __('general.edit'),
            'territoryTypes' => TerritoryType::select('id', 'name->ru as name_t')->get(),
            'countries'      => Country::select('id', 'name->ru as name')->get(),
            'districts'      => District::select('id', 'country_id', 'name->ru as name_d')->where('country_id', $city?->region?->district?->country_id)->get(),
            'regions'        => Region::select('id', 'district_id', 'name->ru as name_r')->where('district_id', $city?->region?->district_id)->get(),
            'data'           => $city->load('region.district.country'),
        ]);
    }

    public function update(AdminCityRequest $request, City $city)
    {
        $city->territory_type_id = $request->territory_type_id;
        $city->region_id         = $request->region_id;
        $city->name              = $request->name;

        return $city->save()
            ? redirect()->route('admin.cities.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.cities.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(City $city)
    {
        return $city->delete()
            ? redirect()->route('admin.cities.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.cities.index')->with('errorMessage', __('general.not_deleted'));
    }
}
