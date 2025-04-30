<?php

namespace App\Http\Controllers\Company;

use Log;
use Auth;
use Misc;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Company\AddModeratorRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User,
    App\Models\Branch,
    App\Models\Permission,
    App\Models\Position,
    App\Models\UserBranche,
    App\Models\UserPosition,
    App\Models\UserStatus;

class ModeratorController extends Controller
{
    public function moderators(Request $request) {
        $companyId = (Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id);

        if(Auth::user()->role == 'MODERATOR' && !Misc::isCanSeeAllBranches()) {
            $brancheIds = Misc::getModeratorBranches();
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->whereIn('id', $brancheIds)->get();
        } else {
            $branches = Branch::select('id', 'title->'. app()->getLocale(). ' as title_b')->where('user_id', $companyId)->get();
        }

        return Inertia::render('Company/Moderators', [
            'title'       => __('general.moderators'),
            'branches'    => $branches,
            'permissions' => Permission::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'moderators'  => User::select('id', 'name', 'email', 'phone')
                                    ->when($request->text, function ($q) use ($request) {
                                        return $q->where(function ($q) use ($request) {
                                            $q->where('name', 'like', '%' . $request->text . '%')
                                                     ->orWhere('email', 'like', '%' . $request->text . '%')
                                                     ->orWhere('phone', 'like', '%' . $request->text . '%');
                                        });

                                    })
                                    ->when($request->roles, function ($q) use ($request) {
                                        return $q->where(function ($q) use ($request) {
                                            $q->whereHas('permissions', function (Builder $query) use ($request) {
                                                $query->whereIn('permission_id', $request->roles);
                                            });
                                        });
                                    })
                                    ->when($request->branches, function ($q) use ($request) {
                                        return $q->where(function ($q) use ($request) {
                                            $q->whereHas('branches', function (Builder $query) use ($request) {
                                                $query->whereIn('branche_id', $request->branches);
                                            });
                                        });
                                    })
                                    ->where('parent_user_id', $companyId)
                                    ->with([
                                        'branches' => function($q) {
                                            $q->select(
                                                'title->'. app()->getLocale(). ' as title_mb',
                                            );
                                        },
                                        'permissions' => function($q) {
                                            $q->select(
                                                'name->'. app()->getLocale(). ' as name_p',
                                            );
                                        }
                                    ])
                                    ->get(),
        ]);
    }

    public function addModerator(AddModeratorRequest $request) {
        if(Auth::user()->role == 'COMPANY' && !Auth::user()->company_name)
            return response()->json(['message' => __('general.not_added')], 500);

        try {
            $user = User::create([
                'role'              => 'MODERATOR',
                'parent_user_id'    => Auth::user()->id,
                'name'              => $request->name,
                'email'             => $request->email,
                'phone'             => $request->phone,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'password'          => Hash::make($request->password),
            ]);

            UserStatus::create([
                'status'          => 'CONFIRMED',
                'user_id'         => $user->id,
                'created_user_id' => Auth::user()->id,
            ]);

            $user->branches()->sync($request->branche_ids);
            $user->permissions()->sync($request->permission_ids);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function updateModerator(AddModeratorRequest $request) {
        try {
            $data = [
                'role'           => 'MODERATOR',
                'parent_user_id' => Auth::user()->id,
                'name'           => $request->name,
                'email'          => $request->email,
                'phone'          => $request->phone,
            ];

            if($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            $user = tap(User::where('id', $request->id))->update($data)->first();

            $user->branches()->sync($request->branche_ids);
            $user->permissions()->sync($request->permission_ids);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_edited')], 500);
        }

        return response()->json(['message' => __('general.successfully_edited')], 201);
    }

    public function deleteModerator(Request $request) {
        try {
            User::where('id', $request->id)->delete();
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_deleted')], 500);
        }

        return response()->json(['message' => __('general.successfully_deleted')], 201);
    }
}
