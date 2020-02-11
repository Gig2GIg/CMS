<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AparencesRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'height'=>'required',
            'weight'=>'required',
            'hair'=>'required',
            'eyes'=>'required',
            'race'=>'required',
            'gender_pronouns'=>'required'
        ];
    }
}
