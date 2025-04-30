<?php

namespace App\Actions\Fortify;

use Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => $this->passwordRules(),
            'terms'         => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            'phone'         => ['required', 'string', 'max:255'],
            'company_name'  => ['required_if:type,==,COMPANY', 'nullable', 'string', 'max:255'],
            'company_phone' => ['required_if:type,==,COMPANY', 'nullable', 'string', 'max:255'],
            'type'          => ['required', 'in:COMPANY,WORKER'],
        ])->validate();

        $user = User::create([
            'role'          => $input['type'],
            'name'          => $input['name'],
            'email'         => $input['email'],
            'phone'         => $input['phone'],
            'company_name'  => $input['company_name'],
            'company_phone' => $input['company_phone'],
            'verify_token'  => Str::random(40),
            'password'      => Hash::make($input['password']),
        ]);


        if($user) {
            Mail::to($input['email'])->send(new VerifyEmail($user));
        }

        return $user;
    }
}
