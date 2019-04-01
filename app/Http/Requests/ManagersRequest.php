<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManagersRequest extends FormRequest
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
            'name'=>           'required',
            'company'=>        'required',
            'type'=>            'required',
            'notifications'=>  'required',
            'email'=>          'required|email'
        ];
    }
}
