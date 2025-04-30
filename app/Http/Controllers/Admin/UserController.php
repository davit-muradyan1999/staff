<?php

namespace App\Http\Controllers\Admin;

use DB;
use Auth;
use Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\AdminAddUserRequest;
use App\Http\Requests\Admin\AdminAddUserStatusRequest;
use App\Traits\UploadFile;
use Inertia\Inertia;
use Storage;
use App\Models\User,
    App\Models\UserStatus,
    App\Models\Branch,
    App\Models\JobApplication,
    App\Models\Permission,
    App\Models\Industry,
    App\Models\QuantityEmploye,
    App\Models\CompanyType,
    App\Models\City,
    App\Models\CompanyInfo,
    App\Models\WorkerInfo,
    App\Models\FieldOfActivity,
    App\Models\AdvantageEmploye,
    App\Models\UserFieldOfActivity,
    App\Models\UserAdvantageEmployee,
    App\Models\Citizenship,
    App\Models\Gender,
    App\Models\DisabilityGroup,
    App\Models\Currency;

class UserController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'files/company_info';
    private $IMAGE_FOLDER_PATH_WORKER = 'files/worker_info';

    public function index(Request $request)
    {
        if(!$request->role) {
            return redirect()->route('admin.dashboard');
        }

        $stage = $request->stage && $request->stage == 'registration' ? 'registration' : 'approved';

        $users = User::select('users.id', 'users.created_at', 'parent_user_id', 'name', 'company_name', 'email', 'company_name', 'phone', 'company_phone', 'profile_photo_path', 'company_photo_path', 'user_statuses.status')->leftjoin('user_statuses', function($join) {
            $join->on('users.id', '=', 'user_statuses.user_id');
            $join->join(DB::raw('(Select max(id) as id from user_statuses group by user_id) LatestRow'), function($join) {
                $join->on('user_statuses.id', '=', 'LatestRow.id');
            });
        })->with([
            'parentUser' => function($query) {
                $query->select('id', 'name', 'company_name');
            },
            'branches',
            'permissions',
            'userSupports'

        ])
        ->whereNot('role', 'ADMIN')
        ->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->text . '%')
                         ->orWhere('email', 'like', '%' . $request->text . '%')
                         ->orWhere('company_phone', 'like', '%' . $request->text . '%')
                         ->orWhere('company_name', 'like', '%' . $request->text . '%')
                         ->orWhere('address_of_residence', 'like', '%' . $request->text . '%')
                         ->orWhere('registration_address', 'like', '%' . $request->text . '%')
                         ->orWhere('phone', 'like', '%' . $request->text . '%');
            });
        })->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->text . '%');
            });
        })->when($request->status, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('user_statuses.status', $request->status);
            });
        })->when($request->company_id, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('users.parent_user_id', $request->company_id);
            });
        })->when($request->branches, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->whereHas('branches', function (Builder $query) use ($request) {
                    $query->whereIn('branche_id', $request->branches);
                });
            });
        })
        ->when($stage, function ($q) use ($stage) {
            return $q->where(function ($q) use ($stage) {
                if($stage == 'registration')
                    $q->whereNull('users.email_verified_at');
                else
                    $q->whereNotNull('users.email_verified_at');
            });
        })
        ->when($request->is_deleted, function ($q) use ($request) {
            return $q->onlyTrashed();
        });

        if(Auth::user()->role == 'ADMIN' || Auth::user()->role == 'ADMINISTRATOR') {
            $users = $users->when($request->role, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->where('role', $request->role);
                });
            });
        }

        if(Auth::user()->role == 'ADMINISTRATOR') {
            if($request->role != 'WORKER' && $request->role != 'COMPANY' && $request->role != 'MODERATOR') {
                return redirect()->route('admin.dashboard');
            }

            if($request->role == 'WORKER' && !in_array(7, Auth::user()->permissions->pluck('id')->toArray())) {
                return redirect()->route('admin.dashboard');
            }

            if($request->role == 'COMPANY' && !in_array(8, Auth::user()->permissions->pluck('id')->toArray())) {
                return redirect()->route('admin.dashboard');
            }

            $users = $users->where(function ($q) {
                $q->whereHas('userSupports', function (Builder $query) {
                    $query->where('support_user_id', Auth::user()->id);
                });
            });
        }

        $users = $users->paginate(50)->appends(request()->query());

        return Inertia::render('Admin/Libraries/Users/List', [
            'title'       => __('general.users'),

            'industries'             => Industry::select('id', 'name->'. app()->getLocale(). ' as name_i')->get(),
            'quantityEmployees'      => QuantityEmploye::select('id', 'name->'. app()->getLocale(). ' as name_q')->get(),
            'companyTypes'           => CompanyType::select('id', 'name->'. app()->getLocale(). ' as name_cp')->get(),
            'cities'                 => City::select('id', 'name->'. app()->getLocale(). ' as name_c')->get(),
            'genders'                => Gender::select('id', 'name->'. app()->getLocale(). ' as name_g')->get(),
            'disabilityGroups'       => DisabilityGroup::select('id', 'name->'. app()->getLocale(). ' as name_d')->get(),
            'advantageEmployees'     => AdvantageEmploye::select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon')->get(),
            'currentYear'            => date('Y'),
            'startYear'              => '1901',
            'fieldOfActivities'      => FieldOfActivity::select('id', 'name->'. app()->getLocale(). ' as name_f')->get(),
            'citizenships'           => Citizenship::select('id', 'name->'. app()->getLocale(). ' as name_c')->orderByRaw('sort = 0, sort ASC')->get(),

            'currencies'             => Currency::select('id', 'name')->where('id', 1)->get(),

            'supportUsers'           => User::select('id', 'name')->whereHas('permissions', function($q) {
                $q->where('permission_id', 7);
            })->where('role', 'ADMINISTRATOR')->get(),
            'supportCompanies'        => User::select('id', 'name')->whereHas('permissions', function($q) {
                $q->where('permission_id', 8);
            })->where('role', 'ADMINISTRATOR')->get(),
            'branches'                => Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $request->company_id ? $request->company_id : [])->get(),
            'permissions'             => Permission::select('id', 'type', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'companies'               => User::select('id', 'company_name')->where('role', 'COMPANY')->get(),
            'users'                   => $users
        ]);
    }

    public function create()
    {
        //
    }

    public function store(AdminAddUserRequest $request)
    {
        try {
            $user                    = new User();
            $user->role              = $request->type;
            $user->name              = $request->name;
            $user->email             = $request->email;
            $user->phone             = $request->phone;
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->password          = Hash::make($request->password);

            if($request->type == 'MODERATOR') {
                $user->parent_user_id = $request->company_id;
            }

            if($request->type == 'COMPANY') {
                $user->company_name = $request->company_name;
                $user->company_phone = $request->company_phone;
            }

            $user->save();

            // UserStatus::create([
            //     'status'          => 'CONFIRMED',
            //     'user_id'         => $user->id,
            //     'created_user_id' => Auth::user()->id,
            // ]);

            if($request->type == 'MODERATOR' || $request->type == 'ADMINISTRATOR') {
                if($request->type == 'MODERATOR') {
                    $user->branches()->sync($request->branche_ids);
                }

                $user->permissions()->sync($request->permission_ids);
            }

            if($request->type == 'WORKER' || $request->type == 'COMPANY' || $request->type == 'MODERATOR') {
                $user->userSupports()->sync($request->user_support_ids);
            }
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);


    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(AdminAddUserRequest $request, User $user)
    {
        try {
            $user->role  = $request->type;
            $user->name  = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;

            if($request->password)
                $user->password = Hash::make($request->password);

            $user->save();
            // $user->parent_user_id = Auth::user()->id;

            // UserStatus::create([
            //     'status'          => 'CONFIRMED',
            //     'user_id'         => $user->id,
            //     'created_user_id' => Auth::user()->id,
            // ]);

            if($request->type == 'MODERATOR' || $request->type == 'ADMINISTRATOR') {
                if($request->type == 'MODERATOR') {
                    $user->branches()->sync($request->branche_ids);
                }

                $user->permissions()->sync($request->permission_ids);
            }

            if($request->type == 'WORKER' || $request->type == 'COMPANY' || $request->type == 'MODERATOR') {
                $user->userSupports()->sync($request->user_support_ids);
            }
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_deleted')], 500);
        }

        return response()->json(['message' => __('general.successfully_deleted')], 201);
    }

    public function getStatuses($user_id) {
        return response()->json([
            'status'  => 'success',
            'userStatuses' => UserStatus::where('user_id', $user_id)->get()
        ]);
    }

    public function getBrancheLists(Request $request) {
        return response()->json([
            'status'   => 'success',
            'userBranche' => user::where('id', $request->user_id)->with([
                'branches' => function($q) {
                    $q->select(
                        'title->'. app()->getLocale(). ' as title_b',
                    );
                }
            ])->first()
        ]);
    }

    public function addStatus(AdminAddUserStatusRequest $request) {
        try {
            $getLastStatus = UserStatus::where('user_id', $request->user_id)->orderBy('id', 'desc')->first();
            if($getLastStatus && $getLastStatus->status == $request->status)
                return response()->json(['message' => __('messages.sameUserStatus')], 500);

            UserStatus::create([
                'status'          => $request->status,
                'user_id'         => $request->user_id,
                'created_user_id' => Auth::user()->id,
            ]);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function getAllEmployees(Request $request) {
        $jobApplicationUserIds = [];
        if($request->job_id) {
            $jobApplicationUserIds = jobApplication::where('job_id', $request->job_id)->pluck('user_id')->toArray();
        }

        $employees = User::where('role', 'WORKER')
                        ->when($request->ignore_existing_employees, function ($q) use ($request, $jobApplicationUserIds) {
                            return $q->whereNotIn('id', $jobApplicationUserIds);
                        })
                        ->get();

        return response()->json([
            'status'    => 'success',
            'employees' => $employees
        ]);
    }

    public function setEmployees(Request $request) {
        try {
            DB::beginTransaction();

            foreach($request->employees as $employee) {
                JobApplication::create([
                    'user_id'       => $employee,
                    'job_id'        => $request->job_id,
                    'company_id'    => $request->company_id,
                    'branche_id'    => $request->branche_id,
                    'is_late_added' => 1,
                ]);
            }


            DB::commit();
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function getCompanyInfo(Request $request) {
        $user_id     = $request->user_id;
        $companyInfo = $this->_getCompanyProfileData($user_id);

        return response()->json([
            'status'                 => 'success',
            'data'                   => $companyInfo,
            'userFieldOfActivities'  => UserFieldOfActivity::where('user_id', $user_id)->with('fieldOfActivity', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_f');
            })->get(),
            'userAdvantageEmployees' => UserAdvantageEmployee::where('user_id', $user_id)->with('advantageEmployee', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon');
            })->get(),
        ]);
    }

    public function getWorkerInfo(Request $request) {
        $user_id    = $request->user_id;
        $workerInfo = $this->_getWorkerProfileData($user_id);

        return response()->json([
            'status' => 'success',
            'data'   => $workerInfo,
        ]);
    }

    public function setWhoAreWe(Request $request) {
        try {
            $user = User::find($request->user_id);

            $user->companyInfo()->updateOrCreate(
                ['user_id'    => $user->id],
                ['who_are_we' => $request->who_are_we]
            );
            return response()->json(['data' => $this->_getCompanyProfileData($user->id), 'message' => __('general.successfully_edited')], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setIndustry(Request $request) {
        try {
            $user = User::find($request->user_id);

            $user->fieldOfActivities()->sync($request->field_of_activities);
            $user->companyInfo()->updateOrCreate(
                ['user_id'    => $user->id],
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
                'data'                  => $this->_getCompanyProfileData($user->id),
                'userFieldOfActivities' => UserFieldOfActivity::where('user_id', $user->id)->with('fieldOfActivity', function($q) {
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
            $user = User::find($request->user_id);

            $user->companyInfo()->updateOrCreate(
                ['user_id'    => $user->id],
                [
                    'email'            => $request->email,
                    'company_site_url' => $request->company_site_url,
                    'lat'              => $request->lat,
                    'lng'              => $request->lng,
                ]
            );

            return response()->json(['data' => $this->_getCompanyProfileData($user->id), 'message' => __('general.successfully_edited')], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setAdvantage(Request $request) {
        try {
            $user = User::find($request->user_id);

            $user->companyInfo()->updateOrCreate(
                ['user_id'    => $user->id],
                [

                ]
            );

            $user->userAdvantageEmployees()->sync($request->data);
            return response()->json([
                'message' => __('general.successfully_edited'),
                'userAdvantageEmployees' => UserAdvantageEmployee::where('user_id', $user->id)->with('advantageEmployee', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon');
                })->get()
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setCard(Request $request) {
        try {
            $user = User::find($request->user_id);

            if($user?->companyInfo && ($request->organization_card || $request->organization_card_remove) && $user->companyInfo->organization_card && Storage::disk('public')->exists($user->companyInfo->organization_card)) Storage::disk('public')->delete($user->companyInfo->organization_card);

            $user->companyInfo()->updateOrCreate(
                ['user_id'    => $user->id],
                [
                    'organization_card' => $request->organization_card ? $this->uploadFile($request->organization_card, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->organization_card_remove ? '' : ($user?->companyInfo ? $user->companyInfo->organization_card : null)),
                    'inn'               => $request->inn,
                    'ogrn'              => $request->ogrn,
                    'kpp'               => $request->kpp,
                    'address'           => $request->address,
                    'okpo'              => $request->okpo,
                    'okved'             => $request->okved,
                    'okopf'             => $request->okopf,
                    'okfs'              => $request->okfs,
                ]
            );

            return response()->json([
                'message' => __('general.successfully_edited'),
                'data'    => $this->_getCompanyProfileData($user->id)
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setCitizenship(Request $request) {
        try {
            if(Auth::user()->role == 'WORKER') {
                $checkWorkerValidation = $this->_checkWorkerUpdateValidation();
                if($checkWorkerValidation)
                    return response()->json(['message' => __('messages.cant_edit_beacuse_confirmed_worker')], 500);
            }

            $user = User::find($request->user_id);

            $data = [
                'citizenship_id' => $request->citizenship_id
            ];

            if($request->citizenship_id == 1) {
                if($user->workerInfo && $user->workerInfo->passport_translation_file_1 && Storage::disk('public')->exists($user->workerInfo->passport_translation_file_1)) Storage::disk('public')->delete($user->workerInfo->passport_translation_file_1);
                if($user->workerInfo && $user->workerInfo->passport_translation_file_2 && Storage::disk('public')->exists($user->workerInfo->passport_translation_file_2)) Storage::disk('public')->delete($user->workerInfo->passport_translation_file_2);
                if($user->workerInfo && $user->workerInfo->passport_translation_file_3 && Storage::disk('public')->exists($user->workerInfo->passport_translation_file_3)) Storage::disk('public')->delete($user->workerInfo->passport_translation_file_3);

                $data['passport_translation_file_1'] = null;
                $data['passport_translation_file_2'] = null;
                $data['passport_translation_file_3'] = null;
            }

            if($request->citizenship_id != 1) {
                if($user->workerInfo && $user->workerInfo->insurance_certificate_file && Storage::disk('public')->exists($user->workerInfo->insurance_certificate_file)) Storage::disk('public')->delete($user->workerInfo->insurance_certificate_file);
                if($user->workerInfo && $user->workerInfo->medical_book_file && Storage::disk('public')->exists($user->workerInfo->medical_book_file)) Storage::disk('public')->delete($user->workerInfo->medical_book_file);

                $data['insurance_certificate_file'] = null;
                $data['medical_book_file'] = null;
            }

            if($request->citizenship_id == 1 || $request->citizenship_id == 2) {
                if($user->workerInfo && $user->workerInfo->migration_card_file_1 && Storage::disk('public')->exists($user->workerInfo->migration_card_file_1)) Storage::disk('public')->delete($user->workerInfo->migration_card_file_1);

                $data['migration_card_file_1'] = null;
            }

            if($request->citizenship_id != 3) {
                if($user->workerInfo && $user->workerInfo->migration_account_file_1 && Storage::disk('public')->exists($user->workerInfo->migration_account_file_1)) Storage::disk('public')->delete($user->workerInfo->migration_account_file_1);

                $data['migration_account_file_1'] = null;
            }

            if($request->citizenship_id != 4) {
                if($user->workerInfo && $user->workerInfo->rvp_file_1 && Storage::disk('public')->exists($user->workerInfo->rvp_file_1)) Storage::disk('public')->delete($user->workerInfo->rvp_file_1);
                if($user->workerInfo && $user->workerInfo->vnj_file_1 && Storage::disk('public')->exists($user->workerInfo->vnj_file_1)) Storage::disk('public')->delete($user->workerInfo->vnj_file_1);
                if($user->workerInfo && $user->workerInfo->payment_check_file_1 && Storage::disk('public')->exists($user->workerInfo->payment_check_file_1)) Storage::disk('public')->delete($user->workerInfo->payment_check_file_1);

                $data['rvp_file_1'] = null;
                $data['vnj_file_1'] = null;
                $data['payment_check_file_1'] = null;
            }

            $user->workerInfo()->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );

            return response()->json([
                'message' => __('general.successfully_edited'),
                'data'    => $this->_getWorkerProfileData($user->id)
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setPassport(Request $request) {
        try {
            if(Auth::user()->role == 'WORKER') {
                $checkWorkerValidation = $this->_checkWorkerUpdateValidation();
                if($checkWorkerValidation)
                    return response()->json(['message' => __('messages.cant_edit_beacuse_confirmed_worker')], 500);
            }

            $user = User::find($request->user_id);

            if(($request->passport_file_1 || $request->passport_file_1_remove) && $user->workerInfo && $user->workerInfo->passport_file_1 && Storage::disk('public')->exists($user->workerInfo->passport_file_1)) Storage::disk('public')->delete($user->workerInfo->passport_file_1);
            if(($request->passport_file_2 || $request->passport_file_2_remove) && $user->workerInfo && $user->workerInfo->passport_file_2 && Storage::disk('public')->exists($user->workerInfo->passport_file_2)) Storage::disk('public')->delete($user->workerInfo->passport_file_2);
            if(($request->passport_file_3 || $request->passport_file_3_remove) && $user->workerInfo && $user->workerInfo->passport_file_3 && Storage::disk('public')->exists($user->workerInfo->passport_file_3)) Storage::disk('public')->delete($user->workerInfo->passport_file_3);


            if(($request->passport_translation_file_1 || $request->passport_translation_file_1_remove) && $user->workerInfo && $user->workerInfo->passport_translation_file_1 && Storage::disk('public')->exists($user->workerInfo->passport_translation_file_1)) Storage::disk('public')->delete($user->workerInfo->passport_translation_file_1);
            if(($request->passport_translation_file_2 || $request->passport_translation_file_2_remove) && $user->workerInfo && $user->workerInfo->passport_translation_file_2 && Storage::disk('public')->exists($user->workerInfo->passport_translation_file_2)) Storage::disk('public')->delete($user->workerInfo->passport_translation_file_2);
            if(($request->passport_translation_file_3 || $request->passport_translation_file_3_remove) && $user->workerInfo && $user->workerInfo->passport_translation_file_3 && Storage::disk('public')->exists($user->workerInfo->passport_translation_file_3)) Storage::disk('public')->delete($user->workerInfo->passport_translation_file_3);

            // if(($request->charter || $request->charter_remove) && $user->companyInfo->charter && Storage::disk('public')->exists($user->companyInfo->charter)) Storage::disk('public')->delete($user->companyInfo->charter);
            // if(($request->charter || $request->charter_remove) && $user->companyInfo->charter && Storage::disk('public')->exists($user->companyInfo->charter)) Storage::disk('public')->delete($user->companyInfo->charter);
            // if(($request->charter || $request->charter_remove) && $user->companyInfo->charter && Storage::disk('public')->exists($user->companyInfo->charter)) Storage::disk('public')->delete($user->companyInfo->charter);
            // if(($request->charter || $request->charter_remove) && $user->companyInfo->charter && Storage::disk('public')->exists($user->companyInfo->charter)) Storage::disk('public')->delete($user->companyInfo->charter);
            // if(($request->charter || $request->charter_remove) && $user->companyInfo->charter && Storage::disk('public')->exists($user->companyInfo->charter)) Storage::disk('public')->delete($user->companyInfo->charter);


            $user->workerInfo()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'passport_file_1'   => $request->passport_file_1 ? $this->uploadFile($request->passport_file_1, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->passport_file_1_remove ? null : ($user->workerInfo ? $user->workerInfo->passport_file_1 : null)),
                    'passport_file_2'   => $request->passport_file_2 ? $this->uploadFile($request->passport_file_2, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->passport_file_2_remove ? null : ($user->workerInfo ? $user->workerInfo->passport_file_2 : null)),
                    'passport_file_3'   => $request->passport_file_3 ? $this->uploadFile($request->passport_file_3, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->passport_file_3_remove ? null : ($user->workerInfo ? $user->workerInfo->passport_file_3 : null)),

                    'passport_translation_file_1'   => $request->passport_translation_file_1 ? $this->uploadFile($request->passport_translation_file_1, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->passport_translation_file_1_remove ? null : ($user->workerInfo ? $user->workerInfo->passport_translation_file_1 : null)),
                    'passport_translation_file_2'   => $request->passport_translation_file_2 ? $this->uploadFile($request->passport_translation_file_2, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->passport_translation_file_2_remove ? null : ($user->workerInfo ? $user->workerInfo->passport_translation_file_2 : null)),
                    'passport_translation_file_3'   => $request->passport_translation_file_3 ? $this->uploadFile($request->passport_translation_file_3, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->passport_translation_file_3_remove ? null : ($user->workerInfo ? $user->workerInfo->passport_translation_file_3 : null)),

                    'first_name'        => $request->first_name,
                    'last_name'         => $request->last_name,
                    'surname'           => $request->surname,
                    'birthday'          => $request->birthday,
                    'place_of_birth'    => $request->place_of_birth,
                    'gender_id'         => $request->gender_id,
                    'series_and_number' => $request->series_and_number,
                    'issued_by'         => $request->issued_by,
                    'date_of_issue'     => $request->date_of_issue,
                    'validity'          => $request->validity,
                    'residence_address' => $request->residence_address,
                ]
            );

            return response()->json([
                'message' => __('general.successfully_edited'),
                'data'    => $this->_getWorkerProfileData($user->id)
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setWorkerFiles(Request $request) {
        try {
            if(Auth::user()->role == 'WORKER') {
                $checkWorkerValidation = $this->_checkWorkerUpdateValidation();
                if($checkWorkerValidation)
                    return response()->json(['message' => __('messages.cant_edit_beacuse_confirmed_worker')], 500);
            }

            $user = User::find($request->user_id);

            if(($request->insurance_certificate_file || $request->insurance_certificate_file_remove) && $user->workerInfo && $user->workerInfo->insurance_certificate_file && Storage::disk('public')->exists($user->workerInfo->insurance_certificate_file)) Storage::disk('public')->delete($user->workerInfo->insurance_certificate_file);
            if(($request->disability_group_file || $request->disability_group_file_remove) && $user->workerInfo && $user->workerInfo->disability_group_file && Storage::disk('public')->exists($user->workerInfo->disability_group_file)) Storage::disk('public')->delete($user->workerInfo->disability_group_file);
            if(($request->snils_file || $request->snils_file_remove) && $user->workerInfo && $user->workerInfo->snils_file && Storage::disk('public')->exists($user->workerInfo->snils_file)) Storage::disk('public')->delete($user->workerInfo->snils_file);
            if(($request->medical_book_file || $request->medical_book_file_remove) && $user->workerInfo && $user->workerInfo->medical_book_file && Storage::disk('public')->exists($user->workerInfo->medical_book_file)) Storage::disk('public')->delete($user->workerInfo->medical_book_file);
            if(($request->migration_card_file_1 || $request->migration_card_file_1_remove) && $user->workerInfo && $user->workerInfo->migration_card_file_1 && Storage::disk('public')->exists($user->workerInfo->migration_card_file_1)) Storage::disk('public')->delete($user->workerInfo->migration_card_file_1);
            if(($request->migration_account_file_1 || $request->migration_account_file_1_remove) && $user->workerInfo && $user->workerInfo->migration_account_file_1 && Storage::disk('public')->exists($user->workerInfo->migration_account_file_1)) Storage::disk('public')->delete($user->workerInfo->migration_account_file_1);
            if(($request->rvp_file_1 || $request->rvp_file_1_remove) && $user->workerInfo && $user->workerInfo->rvp_file_1 && Storage::disk('public')->exists($user->workerInfo->rvp_file_1)) Storage::disk('public')->delete($user->workerInfo->rvp_file_1);
            if(($request->vnj_file_1 || $request->vnj_file_1_remove) && $user->workerInfo && $user->workerInfo->vnj_file_1 && Storage::disk('public')->exists($user->workerInfo->vnj_file_1)) Storage::disk('public')->delete($user->workerInfo->vnj_file_1);
            if(($request->payment_check_file_1 || $request->payment_check_file_1_remove) && $user->workerInfo && $user->workerInfo->payment_check_file_1 && Storage::disk('public')->exists($user->workerInfo->payment_check_file_1)) Storage::disk('public')->delete($user->workerInfo->payment_check_file_1);
            if(($request->inn_file || $request->inn_file_remove) && $user->workerInfo && $user->workerInfo->inn_file && Storage::disk('public')->exists($user->workerInfo->inn_file)) Storage::disk('public')->delete($user->workerInfo->inn_file);

            $user->workerInfo()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'insurance_certificate' => $request->insurance_certificate,
                    'disability_group_id'   => $request->disability_group_id,
                    'medical_book'          => $request->medical_book,
                    'snils'                 => $request->snils,
                    'inn'                   => $request->inn,

                    'insurance_certificate_file' => $request->insurance_certificate_file ? $this->uploadFile($request->insurance_certificate_file, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->insurance_certificate_file_remove ? null : ($user->workerInfo ? $user->workerInfo->insurance_certificate_file : null)),
                    'disability_group_file'      => $request->disability_group_file ? $this->uploadFile($request->disability_group_file, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->disability_group_file_remove ? null : ($user->workerInfo ? $user->workerInfo->disability_group_file : null)),
                    'snils_file'                 => $request->snils_file ? $this->uploadFile($request->snils_file, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->snils_file_remove ? null : ($user->workerInfo ? $user->workerInfo->snils_file : null)),
                    'medical_book_file'          => $request->medical_book_file ? $this->uploadFile($request->medical_book_file, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->medical_book_file_remove ? null : ($user->workerInfo ? $user->workerInfo->medical_book_file : null)),
                    'migration_card_file_1'      => $request->migration_card_file_1 ? $this->uploadFile($request->migration_card_file_1, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->migration_card_file_1_remove ? null : ($user->workerInfo ? $user->workerInfo->migration_card_file_1 : null)),
                    'migration_account_file_1'   => $request->migration_account_file_1 ? $this->uploadFile($request->migration_account_file_1, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->migration_account_file_1_remove ? null : ($user->workerInfo ? $user->workerInfo->migration_account_file_1 : null)),
                    'rvp_file_1'                 => $request->rvp_file_1 ? $this->uploadFile($request->rvp_file_1, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->rvp_file_1_remove ? null : ($user->workerInfo ? $user->workerInfo->rvp_file_1 : null)),
                    'vnj_file_1'                 => $request->vnj_file_1 ? $this->uploadFile($request->vnj_file_1, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->vnj_file_1_remove ? null : ($user->workerInfo ? $user->workerInfo->vnj_file_1 : null)),
                    'payment_check_file_1'       => $request->payment_check_file_1 ? $this->uploadFile($request->payment_check_file_1, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->payment_check_file_1_remove ? null : ($user->workerInfo ? $user->workerInfo->payment_check_file_1 : null)),
                    'inn_file'                   => $request->inn_file ? $this->uploadFile($request->inn_file, $this->IMAGE_FOLDER_PATH_WORKER . '/' . $user->id) : ($request->inn_file_remove ? null : ($user->workerInfo ? $user->workerInfo->inn_file : null)),
                ]
            );

            return response()->json([
                'message' => __('general.successfully_edited'),
                'data'    => $this->_getWorkerProfileData($user->id)
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setBankCard(Request $request) {
        try {
            if(Auth::user()->role == 'WORKER') {
                $checkWorkerValidation = $this->_checkWorkerUpdateValidation();
                if($checkWorkerValidation)
                    return response()->json(['message' => __('messages.cant_edit_beacuse_confirmed_worker')], 500);
            }

            $user = User::find($request->user_id);

            $user->workerInfo()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'currency_id'                => $request->currency_id,
                    'recipient'                  => $request->recipient,
                    'account_number'             => $request->account_number,
                    'recipient_bank'             => $request->recipient_bank,
                    'bik'                        => $request->bik,
                    'correspondent_account'      => $request->correspondent_account,
                    'swift_code'                 => $request->swift_code,



                ]
            );

            return response()->json([
                'message' => __('general.successfully_edited'),
                'data'    => $this->_getWorkerProfileData($user->id)
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setAccount(Request $request) {
        try {
            $user = User::find($request->user_id);

            if($request->checking_account) {
                $companyInfo = CompanyInfo::select('id')->where('user_id', '!=', $user->id)->where('checking_account', $request->checking_account)->first();
                if($companyInfo) {
                    return response()->json(['status' => 'error', 'message'  => __('messages.sameCheckingAccountValidation')], 500);
                }
            }

            if($request->correspondent_account) {
                $companyInfo = CompanyInfo::select('id')->where('user_id', '!=', $user->id)->where('correspondent_account', $request->correspondent_account)->first();
                if($companyInfo) {
                    return response()->json(['status' => 'error', 'message'  => __('messages.sameCorrespondentAccountValidation')], 500);
                }
            }

            $user->companyInfo()->updateOrCreate(
                ['user_id'    => $user->id],
                [
                    'bank_name'             => $request->bank_name,
                    'checking_account'      => $request->checking_account,
                    'correspondent_account' => $request->correspondent_account,
                    'bank_bip'              => $request->bank_bip,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => __('general.successfully_edited'),
                'data'    => $this->_getCompanyProfileData($user->id)
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'error', 'message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setFiles(Request $request) {
        try {
            $user = User::find($request->user_id);

            if($user?->companyInfo && ($request->charter || $request->charter_remove) && $user->companyInfo->charter && Storage::disk('public')->exists($user->companyInfo->charter)) Storage::disk('public')->delete($user->companyInfo->charter);
            if($user?->companyInfo && ($request->tax_certificate || $request->tax_certificate_remove) && $user->companyInfo->tax_certificate && Storage::disk('public')->exists($user->companyInfo->tax_certificate)) Storage::disk('public')->delete($user->companyInfo->tax_certificate);
            if($user?->companyInfo && ($request->extract_egrul || $request->extract_egrul_remove) && $user->companyInfo->extract_egrul && Storage::disk('public')->exists($user->companyInfo->extract_egrul)) Storage::disk('public')->delete($user->companyInfo->extract_egrul);
            if($user?->companyInfo && ($request->passport_gen_director || $request->passport_gen_director_remove) && $user->companyInfo->passport_gen_director && Storage::disk('public')->exists($user->companyInfo->passport_gen_director)) Storage::disk('public')->delete($user->companyInfo->passport_gen_director);
            if($user?->companyInfo && ($request->certificate_of_no_debt || $request->certificate_of_no_debt_remove) && $user->companyInfo->certificate_of_no_debt && Storage::disk('public')->exists($user->companyInfo->certificate_of_no_debt)) Storage::disk('public')->delete($user->companyInfo->certificate_of_no_debt);
            if($user?->companyInfo && ($request->tax_declarations_last_year || $request->tax_declarations_last_year_remove) && $user->companyInfo->tax_declarations_last_year && Storage::disk('public')->exists($user->companyInfo->tax_declarations_last_year)) Storage::disk('public')->delete($user->companyInfo->tax_declarations_last_year);
            if($user?->companyInfo && ($request->tax_declarations_last_period || $request->tax_declarations_last_period_remove) && $user->companyInfo->tax_declarations_last_period && Storage::disk('public')->exists($user->companyInfo->tax_declarations_last_period)) Storage::disk('public')->delete($user->companyInfo->tax_declarations_last_period);

            $user->companyInfo()->updateOrCreate(
                ['user_id'    => $user->id],
                [
                    'charter'                      => $request->charter ? $this->uploadFile($request->charter, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->charter_remove ? null : ($user?->companyInfo ? $user->companyInfo->charter : null)),
                    'tax_certificate'              => $request->tax_certificate ? $this->uploadFile($request->tax_certificate, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->tax_certificate_remove ? null : ($user?->companyInfo ? $user->companyInfo->tax_certificate : null)),
                    'extract_egrul'                => $request->extract_egrul ? $this->uploadFile($request->extract_egrul, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->extract_egrul_remove ? null : ($user?->companyInfo ? $user->companyInfo->extract_egrul : null)),
                    'passport_gen_director'        => $request->passport_gen_director ? $this->uploadFile($request->passport_gen_director, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->passport_gen_director_remove ? null : ($user?->companyInfo ? $user->companyInfo->passport_gen_director : null)),
                    'certificate_of_no_debt'       => $request->certificate_of_no_debt ? $this->uploadFile($request->certificate_of_no_debt, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->certificate_of_no_debt_remove ? null : ($user?->companyInfo ? $user->companyInfo->certificate_of_no_debt : null)),
                    'tax_declarations_last_year'   => $request->tax_declarations_last_year ? $this->uploadFile($request->tax_declarations_last_year, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->tax_declarations_last_year_remove ? null : ($user?->companyInfo ? $user->companyInfo->tax_declarations_last_year : null)),
                    'tax_declarations_last_period' => $request->tax_declarations_last_period ? $this->uploadFile($request->tax_declarations_last_period, $this->IMAGE_FOLDER_PATH . '/' . $user->id) : ($request->tax_declarations_last_period_remove ? null : ($user?->companyInfo ? $user->companyInfo->tax_declarations_last_period : null)),
                ]
            );

            return response()->json([
                'message' => __('general.successfully_edited'),
                'data'    => $this->_getCompanyProfileData($user->id)
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    public function setCompany(Request $request) {
        try {
            $user = User::find($request->user_id);

            $user->userAdvantageEmployees()->sync($request->data);
            return response()->json([
                'message' => __('general.successfully_edited'),
                'userAdvantageEmployees' => UserAdvantageEmployee::where('user_id', $user->id)->with('advantageEmployee', function($q) {
                    $q->select('id', 'name->'. app()->getLocale(). ' as name_a', 'icon');
                })->get()
            ], 201);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message'  => __('general.errorTryAgainLater')], 500);
        }
    }

    private function _getCompanyProfileData($user_id) {
        return CompanyInfo::select(
                '*',
                'who_are_we->'. app()->getLocale(). ' as who_are_we_w',
                'location->'. app()->getLocale(). ' as location_l',
                'inn->'. app()->getLocale(). ' as inn_c',
                'ogrn->'. app()->getLocale(). ' as ogrn_c',
                'kpp->'. app()->getLocale(). ' as kpp_c',

                'address->'. app()->getLocale(). ' as address_c',
                'okpo->'. app()->getLocale(). ' as okpo_c',
                'okved->'. app()->getLocale(). ' as okved_c',
                'okopf->'. app()->getLocale(). ' as okopf_c',
                'okfs->'. app()->getLocale(). ' as okfs_c',


            )->where('user_id', $user_id)->with([
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

    private function _getWorkerProfileData($user_id) {
        return WorkerInfo::select('*')
            ->where('user_id', $user_id)
            ->with('citizenship', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_c');
            })
            ->with('gender', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_g');
            })
            ->with('disabilityGroup', function($q) {
                $q->select('id', 'name->'. app()->getLocale(). ' as name_d');
            })
            ->first();
    }

    private function _checkWorkerUpdateValidation() {
        $user = User::where('id', Auth::user()->id)->with('statusLatest')->first();
        if($user->status_latest && $user->status_latest->status == 'CONFIRMED')
            return true;

        return false;
    }

}
