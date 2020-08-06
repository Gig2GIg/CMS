<?php

namespace App\Http\Requests;

class CalendarRequest extends ApiRequest
{

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
            'production_type' => 'required|string|max:100',
            // 'event_type'=>'required',
            'project_name' => 'required|string',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d'
        ];
    }
}
