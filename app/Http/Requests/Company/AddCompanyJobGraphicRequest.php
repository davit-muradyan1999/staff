<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class AddCompanyJobGraphicRequest extends FormRequest
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
            'job_id'      => ['required', 'numeric', 'min:0', 'not_in:0'],
            'started_at'  => ['required'],
            'finished_at' => ['required'],
        ];
    }
}
