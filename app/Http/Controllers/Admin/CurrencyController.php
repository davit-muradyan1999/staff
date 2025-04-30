<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminCurrencyRequest;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/Currencies/List', [
            'title'      => __('general.currency'),
            'currencies' => Currency::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Currencies/Add', [
            'title' => __('general.currency') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminCurrencyRequest $request)
    {
        $currency       = new Currency();
        $currency->name = $request->name;

        return $currency->save()
            ? redirect()->route('admin.currencies.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.currencies.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Currency $currency)
    {
        return Inertia::render('Admin/Libraries/Currencies/Add', [
            'title' => __('general.currency') . ' / ' . __('general.edit'),
            'data'  => $currency,
        ]);
    }

    public function update(AdminCurrencyRequest $request, Currency $currency)
    {
        $currency->name = $request->name;

        return $currency->save()
            ? redirect()->route('admin.currencies.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.currencies.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Currency $currency)
    {
        return $currency->delete()
            ? redirect()->route('admin.currencies.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.currencies.index')->with('errorMessage', __('general.not_deleted'));
    }
}
