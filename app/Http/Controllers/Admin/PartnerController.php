<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\UploadFile;
use Storage;
use App\Http\Requests\Admin\AdminPartnerRequest;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Builder;

class PartnerController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'images/partners';

    public function index()
    {
        return Inertia::render('Admin/Libraries/Partners/List', [
            'title'    => __('general.partners'),
            'partners' => Partner::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Partners/Add', [
            'title' => __('general.partners') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminPartnerRequest $request)
    {
        $partner              = new Partner();
        $partner->name        = $request->name;
        $partner->description = $request->description;
        $partner->url         = $request->url;
        $partner->logo        = $this->uploadFile($request->logo, $this->IMAGE_FOLDER_PATH);

        return $partner->save()
            ? redirect()->route('admin.partners.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.partners.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Partner $partner)
    {
        return Inertia::render('Admin/Libraries/Partners/Add', [
            'title' => __('general.partners') . ' / ' . __('general.edit'),
            'data'  => $partner,
        ]);
    }

    public function update(AdminPartnerRequest $request, Partner $partner)
    {
        $partner->name        = $request->name;
        $partner->description = $request->description;
        $partner->url         = $request->url;

        if($request->file('logo') || $request->delete_logo) {
            if($partner->logo && Storage::disk('public')->exists($partner->logo))
                Storage::disk('public')->delete($partner->logo);

            $partner->logo = $request->file('logo')
                                            ? $this->uploadFile($request->logo, $this->IMAGE_FOLDER_PATH)
                                            : null;
        }

        return $partner->save()
            ? redirect()->route('admin.partners.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.partners.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Partner $partner)
    {
        if($partner->logo && Storage::disk('public')->exists($partner->logo))
            Storage::disk('public')->delete($partner->logo);

        return $partner->delete()
            ? redirect()->route('admin.partners.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.partners.index')->with('errorMessage', __('general.not_deleted'));
    }
}
