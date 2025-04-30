<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminMeanOfTransportRequest;
use App\Models\MeanOfTransport;

class MeanOfTransportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/MeanOfTransports/List', [
            'title'            => __('general.meanOfTransports'),
            'meanOfTransports' => MeanOfTransport::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/MeanOfTransports/Add', [
            'title' => __('general.meanOfTransports') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminMeanOfTransportRequest $request)
    {
        $meanOfTransport       = new MeanOfTransport();
        $meanOfTransport->name = $request->name;

        return $meanOfTransport->save()
            ? redirect()->route('admin.mean_of_transports.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.mean_of_transports.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(MeanOfTransport $meanOfTransport)
    {
        return Inertia::render('Admin/Libraries/MeanOfTransports/Add', [
            'title' => __('general.meanOfTransports') . ' / ' . __('general.edit'),
            'data'  => $meanOfTransport,
        ]);
    }

    public function update(AdminMeanOfTransportRequest $request, MeanOfTransport $meanOfTransport)
    {
        $meanOfTransport->name = $request->name;

        return $meanOfTransport->save()
            ? redirect()->route('admin.mean_of_transports.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.mean_of_transports.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(MeanOfTransport $meanOfTransport)
    {
        return $meanOfTransport->delete()
            ? redirect()->route('admin.mean_of_transports.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.mean_of_transports.index')->with('errorMessage', __('general.not_deleted'));
    }
}
