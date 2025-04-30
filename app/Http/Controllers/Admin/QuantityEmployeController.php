<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminQuantityEmployeRequest;
use App\Models\QuantityEmploye;

class QuantityEmployeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/QuantityEmployees/List', [
            'title'             => __('general.quantityEmployees'),
            'quantityEmployees' => QuantityEmploye::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/QuantityEmployees/Add', [
            'title' => __('general.quantityEmployees') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminQuantityEmployeRequest $request)
    {
        $quantityEmploye       = new QuantityEmploye();
        $quantityEmploye->name = $request->name;

        return $quantityEmploye->save()
            ? redirect()->route('admin.quantity_employees.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.quantity_employees.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(QuantityEmploye $quantityEmployee)
    {
        return Inertia::render('Admin/Libraries/QuantityEmployees/Add', [
            'title' => __('general.quantityEmployees') . ' / ' . __('general.edit'),
            'data'  => $quantityEmployee,
        ]);
    }

    public function update(AdminQuantityEmployeRequest $request, QuantityEmploye $quantityEmployee)
    {
        $quantityEmployee->name = $request->name;

        return $quantityEmployee->save()
            ? redirect()->route('admin.quantity_employees.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.quantity_employees.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(QuantityEmploye $quantityEmployee)
    {
        return $quantityEmployee->delete()
            ? redirect()->route('admin.quantity_employees.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.quantity_employees.index')->with('errorMessage', __('general.not_deleted'));
    }
}
