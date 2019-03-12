<?php

namespace App\Http\Requests;

class UserRequest extends ApiRequest
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
            'email' => 'required|email|unique:users',
           'password'=>'required',
            'type'=>'required',
            'first_name'=>'required|string|max:50',
            'address'=>'required|max:150',
            'city'=>'required|string|max:50',
            'state'=>'required|integer',
            'birth'=>'required|date',
            'location'=>'required',
            'union_member'=>'required',
            'stage_name'=>'required',
            'profesion'=>'required'
        ];
    }


}
