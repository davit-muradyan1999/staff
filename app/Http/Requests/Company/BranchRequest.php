<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
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
            'title'   => ['required', 'max:255'],
            'city'    => ['required', 'max:255'],
            'phone'   => ['required', 'max:255'],
            'address' => ['required', 'max:255'],
            'lat'     => ['required'],
            'lng'     => ['nullable'],
        ];
    }
}
