<?php

namespace App\Http\Controllers\Auth;

use App\Models\UserLoginHistory;
use Str;
use Hash;
use Auth;
use Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Log;

class SocialAuthController extends Controller
{
    public function yandexRedirect(Request $request)
    {
        return Socialite::driver('yandex')->with(['type' => $request->type])->redirect();
    }

    public function yandexCallback()
    {
        try {
            $yandexUser = Socialite::driver('yandex')->stateless()->user();

            if(isset(request()->session()->get('_previous')['url'])) {
                $parts = parse_url(request()->session()->get('_previous')['url']);
                if(isset($parts['query'])) parse_str($parts['query'], $query);
            }

            if($yandexUser) {
                $yandexUser = (array)$yandexUser;
            }

            $user = User::where('email', $yandexUser['email'])->first();

            if(!$user && !isset($query['type'])) {
                return redirect()->route('home', ['access' => true])->with('errorMessage', __('messages.registerFirstToAccess'));
            }

            if($user) {
                Auth::login($user);
                return redirect('/');
            }
            else {
                $user = new User;
                $user->name = $yandexUser['name'];
                $user->email = $yandexUser['email'];
                $user->password = Hash::make(Str::random(20));
                $user->role = $query['type'];
                $user->type = 'YANDEX';
                $user->email_verified_at = date('Y-m-d H:i:s');

                $user_login_history = new UserLoginHistory;
                $user_login_history->username = $yandexUser['email'];
                $user_login_history->type = 'WEB';
                $user_login_history->social_type = 'YANDEX';
                $user_login_history->user_agent = $_SERVER['HTTP_USER_AGENT'];
                $user_login_history->remote_addr = $_SERVER['REMOTE_ADDR'];
                if($user->save()) {
                    Auth::login($user);
                    $user_login_history->user_id = $user->id;
                    $user_login_history->status = 1;
                    $user_login_history->save();
                    return redirect('/');
                }
            }
        }
        catch (Exception $e) {
            return 'error';
        }
    }

    public function mailruRedirect(Request $request)
    {
        return Socialite::driver('mailru')->with(['type' => $request->type])->redirect();
    }

    public function mailruCallback()
    {
        try {
            $mailruUser = Socialite::driver('mailru')->stateless()->user();

            if(isset(request()->session()->get('_previous')['url'])) {
                $parts = parse_url(request()->session()->get('_previous')['url']);
                if(isset($parts['query'])) parse_str($parts['query'], $query);
            }

            if($mailruUser) {
                $mailruUser = (array)$mailruUser;
            }

            $user = User::where('email', $mailruUser['email'])->first();

            if(!$user && !isset($query['type'])) {
                return redirect()->route('home', ['access' => true])->with('errorMessage', __('messages.registerFirstToAccess'));
            }

            if($user) {
                Auth::login($user);
                return redirect('/');
            }
            else {
                $user = new User;
                $user->name = $mailruUser['name'];
                $user->email = $mailruUser['email'];
                $user->password = Hash::make(Str::random(20));
                $user->role = $query['type'];
                $user->type = 'MAILRU';
                $user->email_verified_at = date('Y-m-d H:i:s');

                $user_login_history = new UserLoginHistory;
                $user_login_history->username = $mailruUser['email'];
                $user_login_history->type = 'WEB';
                $user_login_history->social_type = 'MAILRU';
                $user_login_history->user_agent = $_SERVER['HTTP_USER_AGENT'];
                $user_login_history->remote_addr = $_SERVER['REMOTE_ADDR'];

                if($user->save()) {
                    Auth::login($user);
                    $user_login_history->user_id = $user->id;
                    $user_login_history->status = 1;
                    $user_login_history->save();
                    return redirect('/');
                }
            }
        }
        catch (Exception $e) {
            return 'error';
        }

    }
}
