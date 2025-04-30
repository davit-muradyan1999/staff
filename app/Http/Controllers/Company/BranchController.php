<?php

namespace App\Http\Controllers\Company;

use Log;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Company\BranchRequest;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Branch,
    App\Models\User;

class BranchController extends Controller
{
    public function myBranches() {
        $companyId = (Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id);
        return Inertia::render('Company/MyBraches', [
            'title'         => __('general.myBranches'),
            'branchesCount' => Branch::where('user_id', $companyId)->count(),
            'branches'      => Branch::select(
                'id',
                'title->'. app()->getLocale(). ' as title_n',
                'city->'. app()->getLocale(). ' as city_n',
                'address->'. app()->getLocale(). ' as address_n',
                'phone->'. app()->getLocale(). ' as phone_n',
                'lat',
                'lng',
            )->where('user_id', $companyId)->get(),
        ]);
    }

    public function addBranch(BranchRequest $request) {
        if(Auth::user()->role == 'COMPANY' && !Auth::user()->company_name)
            return response()->json(['message' => __('general.not_added')], 500);

        try {
            Branch::create([
                'user_id' => Auth::user()->id,
                'title'   => $request->title,
                'city'    => $request->city,
                'phone'   => $request->phone,
                'address' => $request->address,
                'lat'     => $request->lat,
                'lng'     => $request->lng,
            ]);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function updateBranch(BranchRequest $request) {
        try {
            Branch::where('id', $request->id)->update([
                'title'   => $request->title,
                'city'    => $request->city,
                'phone'   => $request->phone,
                'address' => $request->address,
                'lat'     => $request->lat,
                'lng'     => $request->lng,
            ]);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function deleteBranch(Request $request) {
    try {
            Branch::where('id', $request->id)->delete();
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_deleted')], 500);
        }

        return response()->json(['message' => __('general.successfully_deleted')], 201);
    }

    public function getModeratorsByBranche(Request $request) {
        return response()->json([
            'status'  => 'success',
            'moderators' => User::select('id', 'name', 'email', 'phone')->whereHas('branches', function (Builder $query) use ($request) {
                $query->where('branche_id', $request->branche_id);
            })->get()
        ]);
    }

    public function getBranchesByCompanyId($company_id) {
        return response()->json([
            'status'   => 'success',
            'branches' => Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $company_id)->get()
        ]);
    }
}
