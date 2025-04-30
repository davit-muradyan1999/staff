<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Traits\UploadFile;
use Storage;
use App\Http\Requests\Admin\AdminAdvantageEmployeRequest;
use App\Models\AdvantageEmploye;
use Illuminate\Database\Eloquent\Builder;

class AdvantageEmployeController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'images/advantageEmployees';

    public function index()
    {
        return Inertia::render('Admin/Libraries/AdvantageEmployees/List', [
            'title'              => __('general.advantageEmployees'),
            'advantageEmployees' => AdvantageEmploye::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/AdvantageEmployees/Add', [
            'title' => __('general.advantageEmployees') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminAdvantageEmployeRequest $request)
    {
        $advantageEmploye       = new AdvantageEmploye();
        $advantageEmploye->name = $request->name;
        $advantageEmploye->icon = $this->uploadFile($request->icon, $this->IMAGE_FOLDER_PATH);

        return $advantageEmploye->save()
            ? redirect()->route('admin.advantage_employees.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.advantage_employees.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(AdvantageEmploye $advantageEmployee)
    {
        return Inertia::render('Admin/Libraries/AdvantageEmployees/Add', [
            'title' => __('general.advantageEmployees') . ' / ' . __('general.edit'),
            'data'  => $advantageEmployee,
        ]);
    }

    public function update(AdminAdvantageEmployeRequest $request, AdvantageEmploye $advantageEmployee)
    {
        $advantageEmployee->name = $request->name;

        if($request->file('icon') || $request->delete_icon) {
            if($advantageEmployee->icon && Storage::disk('public')->exists($advantageEmployee->icon))
                Storage::disk('public')->delete($advantageEmployee->icon);

            $advantageEmployee->icon = $request->file('icon')
                                            ? $this->uploadFile($request->icon, $this->IMAGE_FOLDER_PATH)
                                            : null;
        }

        return $advantageEmployee->save()
            ? redirect()->route('admin.advantage_employees.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.advantage_employees.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(AdvantageEmploye $advantageEmployee)
    {
        if($advantageEmployee->icon && Storage::disk('public')->exists($advantageEmployee->icon))
            Storage::disk('public')->delete($advantageEmployee->icon);

        return $advantageEmployee->delete()
            ? redirect()->route('admin.advantage_employees.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.advantage_employees.index')->with('errorMessage', __('general.not_deleted'));
    }

    public function getAdvantageEmployeesByCompanyId($company_id) {
        return response()->json([
            'status'             => 'success',
            'advantageEmployees' => AdvantageEmploye::select('id', 'name->'. app()->getLocale(). ' as name_a')
                                        ->whereHas('userAdvantageEmployees', function (Builder $query) use($company_id) {
                                            $query->where('user_id', $company_id);
                                        })
                                        ->get()
        ]);
    }
}
