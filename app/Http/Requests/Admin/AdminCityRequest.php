<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminCityRequest extends FormRequest
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
            'territory_type_id' => ['required', 'numeric', 'min:0', 'not_in:0'],
            'region_id'         => ['required', 'numeric', 'min:0', 'not_in:0'],
            'name.ru'           => ['required', 'max:255'],
        ];
    }
}
