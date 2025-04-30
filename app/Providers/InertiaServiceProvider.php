<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Session;

class InertiaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Inertia::share('app.name', config('app.name'));
        Inertia::share('errors', function() {
            return session()->get('errors') ? session()->get('errors')->getBag('default')->getMessages() : (object) [];
        });
        Inertia::share('errorMessage', function() {
            return session()->get('errorMessage') ? session()->get('errorMessage') : null;
        });
        Inertia::share('successMessage', function() {
            return session()->get('successMessage') ? session()->get('successMessage') : null;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
