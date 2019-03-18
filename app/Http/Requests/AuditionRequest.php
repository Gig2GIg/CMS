<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditionRequest extends ApiRequest
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
            'cover'=>'required',
            'union',
            'contract',
            'production',
            'status',
            'user_id'=>'required',
            'roles'=>'required|array',
            'appointment'=>'required',
            'contributors'=>'required|array',
            'media'=>'required|array'
        ];
    }
}
