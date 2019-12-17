<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserTabletEdit extends FormRequest
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
            'email' => 'required|email',
            'name'=>'required|string|max:50',
            'address'=>'required|max:150',
            'city'=>'required|string|max:50',
            'state'=>'required|integer',
            // 'birth'=>'required|date',
//            'location'=>'required',
            'agency_name'=>'required',
            'profesion'=>'required',
            'zip'=>'required'
        ];
    }
}
