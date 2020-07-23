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

        $rules = [
            'title' => 'required',
//            'date'=>'required',
//            'time'=>'required',
            //'location'=>'required',
            'description' => 'required',
//            'url' => 'required',
            'end_date' => 'required_if:online,true|date_format:Y-m-d',
            'cover' => 'required',
            'union',
            'contract',
            'production',
            'status',
            'dates',
            'roles' => 'required|array',
            'online' => 'required',
            'rounds' => 'required|array',
            'rounds.*.appointment' => 'required',
            'rounds.*.grouping_enabled' => 'required',
            'rounds.*.grouping_capacity' => 'required_if:rounds.*.grouping_enabled,true',
            'rounds.*.date' => 'required_if:online,false',
            'rounds.*.time' => 'required_if:online,false',
            'rounds.*.location' => 'required_if:online,false'
        ];

        return $rules;
    }
}
