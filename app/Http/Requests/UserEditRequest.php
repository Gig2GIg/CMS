<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password'=>'required',
            'first_name'=>'required|string|max:50',
            'address'=>'required|max:150',
            'city'=>'required|string|max:50',
            'state'=>'required|integer',
            'birth'=>'required|date',
            'location'=>'required',
            'stage_name'=>'required',
            'profesion'=>'required',
            'zip'=>'required'
        ];
    }
}
