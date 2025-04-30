<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLoginHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserLoginHistoryController extends Controller
{
    public function index(){
        return Inertia::render('Admin/Libraries/LoginHistory/List', [
            'title'     => __('general.positions'),
            'loginHistory' => UserLoginHistory::all(),
        ]);
    }

}
