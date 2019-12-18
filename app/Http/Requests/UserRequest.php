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
        if(request('type') === '1'){
            return [
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'type' => 'required',
                'name' => 'required|string|max:150',
                'address' => 'required|max:150',
                'city' => 'required|string|max:50',
                'state' => 'required|integer',
                // 'birth' => 'required',
//                'location' => 'required',
                'zip' => 'required',
                'agency_name' => 'required',
                'image' => 'required|url',
                'profesion' => 'required'
            ];
        }else{
            return [
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'type' => 'required',
                'first_name' => 'required|string|max:50',
                'address' => 'required|max:150',
                'city' => 'required|string|max:50',
                'state' => 'required|integer',
                // 'birth' => 'required',
//                'location' => 'required',
                'zip' => 'required',
                'union_member' => 'required',
//                'stage_name' => 'required',
                'image' => 'required|url',
                'profesion' => 'required'
            ];
        }

    }


}
