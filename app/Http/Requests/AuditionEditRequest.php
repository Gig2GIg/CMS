<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditionEditRequest extends ApiRequest
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
            'title'=>'required',
            'date'=>'required',
            'time'=>'required',
            'location'=>'required',
            'description'=>'required',
            'url'=>'required',

            'union',
            'contract',
            'production',
            'status',
            'dates',
            'roles'=>'required|array',
            'appointment',
            'media'
        ];
    }
}
