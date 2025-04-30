<?php

namespace App\Http\Controllers\Company;

use Log;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Hash;
use Storage;
use App\Models\User,
    App\Models\CompanyInfo,
    App\Models\Industry,
    App\Models\QuantityEmploye,
    App\Models\CompanyType,
    App\Models\City,
    App\Models\AdvantageEmploye,
    App\Models\UserAdvantageEmployee,
    App\Models\FieldOfActivity,
    App\Models\UserFieldOfActivity;

class ProfileController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'files/company_info';

    public function companyProfile(Request $request) {
        $userId = Auth::user()->id;
        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;
        $template = 'Company/Profile';

        if(in_array(Auth::user()->role, ['ADMIN', 'ADMINISTRATOR', 'WORKER'])) {
            if(!$request->id)
                return redirect()->back();

            $user = User::find($request->id);
            if(!$user || $user->role != 'COMPANY')
                return redirect()->back();

            $userId = $request->id;
            $companyId = $request->id;

            if(Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') {
                $template = 'Company/ProfileAdminView';
            }
        }

        return Inertia::render($template, [
            'title'                  => __('general.profile'),
            'industries'             => Industry::select('id', 'name->'. app()->getLocale(). ' as name_i')->get(),
            'quantityEmployees'      => QuantityEmploye::select('id', 'name->'. app()->getLocale(). ' as name_q')->get(),
            'companyTypes'           => CompanyType::select('id', 'name->'. app()->getLocale(). ' as name_cp')->get(),
            'cities'                 => City::select('id', 'name->'. app()->getLocale(). ' as name_c')->get(),
            'advantageEmployees'     => AdvantageEmploye::select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon')->get(),
            'currentYear'            => date('Y'),
            'startYear'              => '1900',
            'info'                   => $this->_getProfileData($companyId),
            'userData'               => $this->_getUserData($userId),
            'companyData'            => $this->_getUserData($companyId),
            'fieldOfActivities'      => FieldOfActivity::select('id', 'name->'. app()->getLocale(). ' as name_f')->get(),
            'userFieldOfActivities'  => UserFieldOfActivity::where('user_id', $userId)->with('fieldOfActivity', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_f');
            })->get(),
            'userAdvantageEmployees' => UserAdvantageEmployee::where('user_id', $userId)->with('advantageEmployee', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon');
            })->get(),
        ]);
    }

    public function setWhoAreWe(Request $request) {
        try {
            Auth::user()->companyInfo()->updateOrCreate(
                ['user_id'    => Auth::user()->id],
                ['who_are_we' => $request->who_are_we]
            );
            return response()->json(['data' => $this->_getProfileData(), 'message' => __('general.successfully_edited')], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setIndustry(Request $request) {
        try {
            Auth::user()->fieldOfActivities()->sync($request->field_of_activities);
            Auth::user()->companyInfo()->updateOrCreate(
                ['user_id'    => Auth::user()->id],
                [
                    'industry_id'          => $request->industry_id,
                    'quantity_employee_id' => $request->quantity_employee_id,
                    'company_type_id'      => $request->company_type_id,
                    'city_id'              => $request->city_id,
                    'foundation_date'      => $request->foundation_date,
                    'location'             => $request->location,
                ]
            );

            return response()->json([
                'message'               => __('general.successfully_edited'),
                'data'                  => $this->_getProfileData(),
                'userFieldOfActivities' => UserFieldOfActivity::where('user_id', Auth::user()->id)->with('fieldOfActivity', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_f');
                })->get()
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setContact(Request $request) {
        try {
            Auth::user()->companyInfo()->updateOrCreate(
                ['user_id'    => Auth::user()->id],
                [
                    'email'            => $request->email,
                    'company_site_url' => $request->company_site_url,
                    'lat'              => $request->lat,
                    'lng'              => $request->lng,
                ]
            );

            return response()->json(['data' => $this->_getProfileData(), 'message' => __('general.successfully_edited')], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setAdvantage(Request $request) {
        try {
            Auth::user()->userAdvantageEmployees()->sync($request->data);
            return response()->json([
                'message' => __('general.successfully_edited'),
                'userAdvantageEmployees' => UserAdvantageEmployee::where('user_id', Auth::user()->id)->with('advantageEmployee', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon');
                })->get()
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setCompanyUserInfo(Request $request) {
        $user = User::find(Auth::user()->id);

        if(($request->img || $request->img_remove) && $user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) Storage::disk('public')->delete($user->profile_photo_path);

        try {
            $user->update([
                'name'               => $request->name,
                'email'              => $request->email,
                'phone'              => $request->phone,
                'profile_photo_path' => $request->img ? $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->img_remove ? '' : $user->profile_photo_path),
                'password'           => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            return response()->json(['data' => $this->_getUserData($user->id), 'message' => __('general.successfully_edited')], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setCompanyInfo(Request $request) {
        $user = User::find(Auth::user()->id);

        if(($request->img || $request->img_remove) && $user->company_photo_path && Storage::disk('public')->exists($user->company_photo_path)) Storage::disk('public')->delete($user->company_photo_path);

        try {
            $user->update([
                'company_name'       => $request->company_name,
                'company_phone'      => $request->company_phone,
                'company_photo_path' => $request->img ? $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->img_remove ? '' : $user->company_photo_path),
            ]);

            return response()->json(['data' => $this->_getUserData($user->id), 'message' => __('general.successfully_edited')], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    private function _getProfileData($userId = null) {
        if(!$userId) $userId = Auth::user()->id;
        return CompanyInfo::select('*', 'who_are_we->'. app()->getLocale(). ' as who_are_we_w', 'location->'. app()->getLocale(). ' as location_l')->where('user_id', $userId)->with([
            'industry' => function($query) {
                $query->select('id', 'name->ru as name_i');
            },
            'quantityEmployee' => function($query) {
                $query->select('id', 'name->ru as name_q');
            },
            'companyType' => function($query) {
                $query->select('id', 'name->ru as name_c');
            },
            'city' => function($query) {
                $query->select('id', 'name->ru as name_c');
            },
            'contactCity' => function($query) {
                $query->select('id', 'name->ru as name_cc');
            }
        ])->first();
    }

    private function _getUserData($userId) {
        if(!$userId) $userId = Auth::user()->id;
        return User::select('name', 'email', 'phone', 'company_name', 'company_phone', 'profile_photo_path', 'company_photo_path')
            ->where('id', $userId)
            ->first();
    }
}
