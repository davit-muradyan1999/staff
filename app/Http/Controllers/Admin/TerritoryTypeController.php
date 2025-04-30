<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminTerritoryTypeRequest;
use App\Models\TerritoryType;

class TerritoryTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/TerritoryTypes/List', [
            'title'          => __('general.territorialDivisionType'),
            'territoryTypes' => TerritoryType::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/TerritoryTypes/Add', [
            'title' => __('general.territorialDivisionType') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminTerritoryTypeRequest $request)
    {
        $territoryType       = new TerritoryType();
        $territoryType->name = $request->name;

        return $territoryType->save()
            ? redirect()->route('admin.territory_types.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.territory_types.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(TerritoryType $territoryType)
    {
        return Inertia::render('Admin/Libraries/TerritoryTypes/Add', [
            'title' => __('general.territorialDivisionType') . ' / ' . __('general.edit'),
            'data'  => $territoryType,
        ]);
    }

    public function update(AdminTerritoryTypeRequest $request, TerritoryType $territoryType)
    {
        $territoryType->name = $request->name;

        return $territoryType->save()
            ? redirect()->route('admin.territory_types.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.territory_types.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(TerritoryType $territoryType)
    {
        return $territoryType->delete()
            ? redirect()->route('admin.territory_types.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.territory_types.index')->with('errorMessage', __('general.not_deleted'));
    }
}
