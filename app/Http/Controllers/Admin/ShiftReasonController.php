<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminShiftReasonRequest;
use App\Models\ShiftReason;

class ShiftReasonController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Libraries/ShiftReasons/List', [
            'title'        => __('general.reasonsForShiftChange'),
            'shiftReasons' => ShiftReason::where('type', '!=', 'OTHER')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/ShiftReasons/Add', [
            'title' => __('general.reasonsForShiftChange') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminShiftReasonRequest $request)
    {
        $shiftReason       = new ShiftReason();
        $shiftReason->type = $request->type;
        $shiftReason->name = $request->name;

        return $shiftReason->save()
            ? redirect()->route('admin.shift_reasons.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.shift_reasons.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(ShiftReason $shiftReason)
    {
        return Inertia::render('Admin/Libraries/ShiftReasons/Add', [
            'title' => __('general.reasonsForShiftChange') . ' / ' . __('general.edit'),
            'data'  => $shiftReason,
        ]);
    }

    public function update(AdminShiftReasonRequest $request, ShiftReason $shiftReason)
    {
        $shiftReason->type = $request->type;
        $shiftReason->name = $request->name;

        return $shiftReason->save()
            ? redirect()->route('admin.shift_reasons.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.shift_reasons.index')->with('errorMessage', __('general.not_added'));
    }

    public function destroy(ShiftReason $shiftReason)
    {
        return $shiftReason->delete()
            ? redirect()->route('admin.shift_reasons.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.shift_reasons.index')->with('errorMessage', __('general.not_deleted'));
    }
}
