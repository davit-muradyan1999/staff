<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAddUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $passwordRule = [];
        $passwordConfirmRule = [];
        if(!request()->input('id')) {
            $passwordRule = ['required'];
            $passwordConfirmRule = ['same:password'];
        }

        return [
            'name'             => ['required', 'max:255'],
            'email'            => ['required', 'max:255', 'email', Rule::unique('users')->ignore($this->id)],
            'phone'            => ['required', 'max:255'],
            'password'         => $passwordRule,
            'password_confirm' => $passwordConfirmRule
        ];
    }
}
