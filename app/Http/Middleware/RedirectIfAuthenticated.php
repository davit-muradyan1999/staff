<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;
        $role = Auth::user() ? Auth::user()->role : null;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                switch ($role) {
                    case 'ADMIN':
                        return redirect()->route('admin.dashboard');
                        break;
                  case 'WORKER':
                        return redirect()->route('dashboard');
                        break;
                    case 'COMPANY':
                        return redirect()->route('dashboard');
                        break;
                    default:
                        return redirect(RouteServiceProvider::HOME);
                        break;
                }
            }
        }

        return $next($request);
    }
}
