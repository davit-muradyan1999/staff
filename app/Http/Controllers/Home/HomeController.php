<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Information,
    App\Models\Partner;

class HomeController extends Controller
{
    public function home() {
        return Inertia::render('Home/Home', [
            'title'        => 'Home',
            'informations' => Information::select(
                'id',
                'title->'. app()->getLocale(). ' as title_n',
                'name->'. app()->getLocale(). ' as name_n',
                'description->'. app()->getLocale(). ' as description_n',
                'info->'. app()->getLocale(). ' as info_n',
                'img'
            )->get(),
            'partners' => Partner::select(
                'id',
                'name->'. app()->getLocale(). ' as name_n',
                'description->'. app()->getLocale(). ' as description_n',
                'url',
                'logo'
            )->get()
        ]);
    }
}
