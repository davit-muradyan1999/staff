<?php

namespace App\Http\Requests\Personal;

use Illuminate\Foundation\Http\FormRequest;

class SetInformationRequest extends FormRequest
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
        return [
            'name'                 => ['required', 'max:255'],
            'birthday'             => ['required', 'date'],
            'citizenship_id'       => ['required', 'numeric', 'min:0', 'not_in:0'],
            'gender_id'            => ['required', 'numeric', 'min:0', 'not_in:0'],
            'registration_address' => ['required', 'max:255'],
            'medical_card_number'  => ['nullable', 'max:255'],
            'phone'                => ['nullable', 'max:255'],
        ];
    }
}
