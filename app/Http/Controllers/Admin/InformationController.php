<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Traits\UploadFile;
use Storage;
use App\Http\Requests\Admin\AdminInformationRequest;
use App\Models\Information;

class InformationController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'images/home';

    public function index()
    {
        return Inertia::render('Admin/Libraries/Informations/List', [
            'title'        => __('general.information'),
            'informations' => Information::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Admin/Libraries/Informations/Add', [
            'title' => __('general.information') . ' / ' . __('general.add'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminInformationRequest $request)
    {
        $information              = new Information();
        $information->title       = $request->title;
        $information->name        = $request->name;
        $information->description = $request->description;
        $information->info        = $request->info;
        $information->img         = $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH);

        return $information->save()
            ? redirect()->route('admin.informations.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.informations.index')->with('errorMessage', __('general.not_added'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Information $information)
    {
        return Inertia::render('Admin/Libraries/Informations/Add', [
            'title' => __('general.information') . ' / ' . __('general.edit'),
            'data'  => $information,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AdminInformationRequest $request, Information $information)
    {
        $information->title       = $request->title;
        $information->name        = $request->name;
        $information->description = $request->description;
        $information->info        = $request->info;

        if($request->file('img') || $request->delete_img) {
            if($information->img && Storage::disk('public')->exists($information->img))
                Storage::disk('public')->delete($information->img);

            $information->img = $request->file('img')
                                            ? $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH)
                                            : null;
        }

        return $information->save()
            ? redirect()->route('admin.informations.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.informations.index')->with('errorMessage', __('general.not_edited'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Information $information)
    {
        if($information->img && Storage::disk('public')->exists($information->img))
                Storage::disk('public')->delete($information->img);

        return $information->delete()
            ? redirect()->route('admin.informations.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.informations.index')->with('errorMessage', __('general.not_deleted'));
    }
}
