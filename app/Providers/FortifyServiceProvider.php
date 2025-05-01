<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\UserLoginHistory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Log;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($request->email)
            {
                $user_login_history = new UserLoginHistory;
                $user_login_history->username = $request->email;
                $user_login_history->type = 'WEB';
                $user_login_history->user_agent = $_SERVER['HTTP_USER_AGENT'];
                $user_login_history->remote_addr = $_SERVER['REMOTE_ADDR'];
            }
            if(!$user) {
                return session()->flash('errorMessage', __('auth.failed'));
                return false;
            }

            if(!$user->email_verified_at) {
                return session()->flash('errorMessage', __('messages.confirmYourEmailToLogIn'));
                return false;
            }
            if($user && Hash::check($request->password, $user->password)) {
                $user_login_history->user_id = $user->id;
                $user_login_history->status = 1;
                $user_login_history->save();

                return $user;
            }

            return session()->flash('errorMessage', __('auth.failed'));
            return false;
        });

        app()->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Actions\Fortify\RegisterResponse::class,
        );
    }
}
