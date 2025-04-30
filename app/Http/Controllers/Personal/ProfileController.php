<?php

namespace App\Http\Controllers\Personal;

use Log;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Storage;
use App\Models\Position,
    App\Models\UserPosition,
    App\Models\UserExperience,
    App\Models\User,
    App\Models\FieldOfActivity,
    App\Models\UserFieldOfActivity,
    App\Models\Education,
    App\Models\UserEducation,
    App\Models\ProfessionalSkill,
    App\Models\UserProfessionalSkill,
    App\Models\PersonalSkill,
    App\Models\UserPersonalSkill,
    App\Models\LanguageSkill,
    App\Models\UserLanguageSkill,
    App\Models\DriverLicense,
    App\Models\UserDriverLicense,
    App\Models\Hobby,
    App\Models\UserHobby;

class ProfileController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'files/worker_info';

    public function profile(Request $request) {
        $userId = Auth::user()->id;
        $template = 'Personal/Profile';

        if(in_array(Auth::user()->role, ['ADMIN', 'ADMINISTRATOR', 'COMPANY'])) {
            if(!$request->id)
                return redirect()->back();

            $user = User::find($request->id);
            if(!$user || $user->role != 'WORKER')
                return redirect()->back();

            $userId = $request->id;

            if(Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') {
                $template = 'Personal/ProfileAdminView';
            }
        }

        return Inertia::render($template, [
            'title'                  => __('general.profile'),
            'userData'               => $this->_getUserData($userId),
            'positions'              => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'userPositions'          => UserPosition::where('user_id', $userId)->with('position', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_p');
            })->get(),
            'fieldOfActivities'      => FieldOfActivity::select('id', 'name->'. app()->getLocale(). ' as name_f')->get(),
            'userFieldOfActivities'  => UserFieldOfActivity::where('user_id', $userId)->with('fieldOfActivity', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_f');
            })->get(),
            'educations'             => Education::select('id', 'name->'. app()->getLocale(). ' as name_e')->get(),
            'userEducations'         => UserEducation::where('user_id', $userId)->with('education', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_e');
            })->get(),
            'professionalSkills'     => ProfessionalSkill::select('id', 'name->'. app()->getLocale(). ' as name_s')->get(),
            'userProfessionalSkills' => UserProfessionalSkill::where('user_id', $userId)->with('professionalSkill', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_s');
            })->get(),
            'personalSkills'         => PersonalSkill::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'userPersonalSkills'     => UserPersonalSkill::where('user_id', $userId)->with('personalSkill', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_p');
            })->get(),
            'languageSkills'         => LanguageSkill::select('id', 'name->'. app()->getLocale(). ' as name_l')->get(),
            'userLanguageSkills'     => UserLanguageSkill::where('user_id', $userId)->with('languageSkill', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_l');
            })->get(),
            'driverLicenses'         => DriverLicense::select('id', 'name->'. app()->getLocale(). ' as name_d')->get(),
            'userDriverLicenses'     => UserDriverLicense::where('user_id', $userId)->with('driverLicense', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_d');
            })->get(),
            'hobbies'                => Hobby::select('id', 'name->'. app()->getLocale(). ' as name_h')->get(),
            'userHobbies'            => UserHobby::where('user_id', $userId)->with('hobby', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_h');
            })->get(),
            'userExperiences'        => UserExperience::select('id', 'organization->'. app()->getLocale(). ' as organization_e', 'position->'. app()->getLocale(). ' as position_e', 'started_at', 'finished_at', 'is_active_work')
                                                        ->where('user_id', $userId)
                                                        ->get(),
        ]);
    }

    public function setPreferredPositions(Request $request) {
        try {
            Auth::user()->positions()->sync($request->data);
            return response()->json([
                'message'       => __('general.successfully_edited'),
                'userPositions' => UserPosition::where('user_id', Auth::user()->id)->with('position', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_p');
                })->get(),
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setExperiences(Request $request) {
        try {
            Auth::user()->userExperiences()->delete();
            Auth::user()->userExperiences()->createMany($request->data);
            return response()->json([
                'message'         => __('general.successfully_edited'),
                'userExperiences' => UserExperience::select('id', 'organization->'. app()->getLocale(). ' as organization_e', 'position->'. app()->getLocale(). ' as position_e', 'started_at', 'finished_at', 'is_active_work')
                                                        ->where('user_id', Auth::user()->id)
                                                        ->get(),
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setFieldOfActivities(Request $request) {
        try {
            Auth::user()->fieldOfActivities()->sync($request->data);
            return response()->json([
                'message'                => __('general.successfully_edited'),
                'userFieldOfActivities'  => UserFieldOfActivity::where('user_id', Auth::user()->id)->with('fieldOfActivity', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_f');
                })->get()
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setEducations(Request $request) {
        try {
            Auth::user()->educations()->sync($request->data);
            return response()->json([
                'message' => __('general.successfully_edited'),
                'userEducations'  => UserEducation::where('user_id', Auth::user()->id)->with('education', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_e');
                })->get()
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setProfessionalSkills(Request $request) {
        try {
            Auth::user()->professionalSkills()->sync($request->data);
            return response()->json([
                'message'                => __('general.successfully_edited'),
                'userProfessionalSkills' => UserProfessionalSkill::where('user_id', Auth::user()->id)->with('professionalSkill', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_s');
                })->get(),
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setPersonalSkills(Request $request) {
        try {
            Auth::user()->personalSkills()->sync($request->data);
            return response()->json([
                'message'            => __('general.successfully_edited'),
                'userPersonalSkills' => UserPersonalSkill::where('user_id', Auth::user()->id)->with('personalSkill', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_p');
                })->get(),
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setLanguageSkills(Request $request) {
        try {
            Auth::user()->languageSkills()->sync($request->data);
            return response()->json([
                'message'            => __('general.successfully_edited'),
                'userLanguageSkills' => UserLanguageSkill::where('user_id', Auth::user()->id)->with('languageSkill', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_l');
                })->get(),
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setDriverLicenses(Request $request) {
        try {
            Auth::user()->driverLicenses()->sync($request->data);
            return response()->json([
                'message'            => __('general.successfully_edited'),
                'userDriverLicenses' => UserDriverLicense::where('user_id', Auth::user()->id)->with('driverLicense', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_d', 'icon');
                })->get(),
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setHobbies(Request $request) {
        try {
            Auth::user()->hobbies()->sync($request->data);
            return response()->json([
                'message'     => __('general.successfully_edited'),
                'userHobbies' => UserHobby::where('user_id', Auth::user()->id)->with('hobby', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_h');
                })->get(),
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setUserInfo(Request $request) {
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

    private function _getUserData($userId) {
        return User::select('name', 'email', 'phone', 'profile_photo_path')
            ->where('id', $userId)
            ->first();
    }
}
