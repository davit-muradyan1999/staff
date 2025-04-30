<?php

namespace App\Http\Middleware;

use Auth;
use Misc;
use Closure;
use Illuminate\Http\Request;

class IsCanSeeModerator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::user()->role == 'MODERATOR') {
            if(!Misc::isCanSeeAllBranches()) {
                return redirect('/');
            }
        }
        return $next($request);
    }
}
