<?php

namespace App\Http\Controllers\Personal;

use Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Personal\SetInformationRequest;
use Inertia\Inertia;
use App\Models\User,
    App\Models\Citizenship,
    App\Models\Gender,
    App\Models\City,
    App\Models\DisabilityGroup;

class InformationController extends Controller
{
    public function informations() {
        return Inertia::render('Personal/Information', [
            'title'            => 'Information',
            'user'             => User::select('*')->where('id', Auth::user()->id)->with('statusLatest')->first(),
            'cities'           => City::select('id', 'name->'. app()->getLocale(). ' as name_c')->get(),
            'genders'          => Gender::select('id', 'name->'. app()->getLocale(). ' as name_g')->get(),
            'disabilityGroups' => DisabilityGroup::select('id', 'name->'. app()->getLocale(). ' as name_d')->get(),
            'citizenships'     => Citizenship::select('id', 'name->'. app()->getLocale(). ' as name_c')->orderByRaw('sort = 0, sort ASC')->get(),
        ]);
    }

    public function setInformation(SetInformationRequest $request) {
        try {
            Auth::user()->update([
                'name'                 => $request->name,
                'birthday'             => $request->birthday,
                'citizenship_id'       => $request->citizenship_id,
                'gender_id'            => $request->gender_id,
                'registration_address' => $request->registration_address,
                'medical_card_number'  => $request->medical_card_number,
                'address_of_residence' => $request->address_of_residence,
                'phone'                => $request->phone,
            ]);
            return redirect()->route('informations')->with('successMessage', __('general.successfully_edited'));
        } catch(\Exception $e) {
            return redirect()->route('informations')->with('successMessage', __('general.errorTryAgainLater'));
        }
    }
}
