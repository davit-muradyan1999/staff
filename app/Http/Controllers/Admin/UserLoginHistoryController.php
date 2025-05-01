<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLoginHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserLoginHistoryController extends Controller
{
    public function index(Request $request){
        $startedAt = $request->startedAt ? date('Y-m-d', strtotime($request->startedAt)) : date('Y-m-01');
        $finishedAt = $request->finishedAt ? date('Y-m-d', strtotime($request->finishedAt)) : date('Y-m-d');

        $loginHistory = UserLoginHistory::with(
            'user'
        )->when($request->text, function ($q) use ($request) {
            return $q->where('username','like', '%' . $request->text . '%' );
        })->when($request->status, function ($q) use ($request) {
            return $q->where('status', $request->status);
        })->when($request->roles, function ($q) use ($request) {
            return $q->whereHas('user', function ($query) use ($request) {
                $query->where('role', $request->roles);
            });
        })->when($startedAt, function ($q) use ($startedAt) {
            return $q->whereDate('created_at', '>=', $startedAt);
        })->when($finishedAt, function ($q) use ($finishedAt) {
            return $q->whereDate('created_at', '<=', $finishedAt);
        })
        ->get();
        return Inertia::render('Admin/Libraries/LoginHistory/List', [
            'title'     => __('general.positions'),
            'loginHistory' => $loginHistory,
        ]);
    }

}
