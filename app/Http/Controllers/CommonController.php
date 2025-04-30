<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CommonController extends Controller
{
    public function verifyEmailToken($token) {
        $user = User::where('verify_token', $token)->first();

        if($user) {
            $user->verify_token = null;
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->save();
            return redirect()->route('home', ['verify' => true])->with('successMessage', __('messages.email_successfully_verified'));
        }

        return redirect()->route('home');
    }
}
