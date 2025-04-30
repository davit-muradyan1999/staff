<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Admin\AdminPositionRequest;
use App\Models\Position;
use Illuminate\Support\Facades\Http;

class PositionController extends Controller
{
    public function index()
    {

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'https://business.tbank.ru/openapi/sandbox/api/v1/statement?accountNumber=40702810510000710417&from=2024-02-01T21:00:00Z',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_HTTPHEADER => array(
        //         'Accept: application/json',
        //         'Authorization: Bearer TBankSandboxToken'
        //     ),
        // ));

        // $response = curl_exec($curl);

        // curl_close($curl);
        // echo $response;

        // die;

        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer TBankSandboxToken',
        //     'Accept' => 'application/json',
        // ])->get('https://business.tbank.ru/openapi/sandbox/api/v1/statement', [
        //     'accountNumber' => '40702810510000710417',
        //     'from' => '2024-02-01T21:00:00Z',
        // ])->json();

        // dd($response);

        return Inertia::render('Admin/Libraries/Positions/List', [
            'title'     => __('general.positions'),
            'positions' => Position::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Libraries/Positions/Add', [
            'title' => __('general.positions') . ' / ' . __('general.add'),
        ]);
    }

    public function store(AdminPositionRequest $request)
    {
        $position       = new Position();
        $position->name = $request->name;

        return $position->save()
            ? redirect()->route('admin.positions.index')->with('successMessage', __('general.successfully_added'))
            : redirect()->route('admin.positions.index')->with('errorMessage', __('general.not_added'));
    }

    public function show($id)
    {
        //
    }

    public function edit(Position $position)
    {
        return Inertia::render('Admin/Libraries/Positions/Add', [
            'title' => __('general.positions') . ' / ' . __('general.edit'),
            'data'  => $position,
        ]);
    }

    public function update(AdminPositionRequest $request, Position $position)
    {
        $position->name = $request->name;

        return $position->save()
            ? redirect()->route('admin.positions.index')->with('successMessage', __('general.successfully_edited'))
            : redirect()->route('admin.positions.index')->with('errorMessage', __('general.not_edited'));
    }

    public function destroy(Position $position)
    {
        return $position->delete()
            ? redirect()->route('admin.positions.index')->with('successMessage', __('general.successfully_deleted'))
            : redirect()->route('admin.positions.index')->with('errorMessage', __('general.not_deleted'));
    }
}
