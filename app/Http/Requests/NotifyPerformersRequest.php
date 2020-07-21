<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotifyPerformersRequest extends FormRequest
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
            'audition_id' => 'required',
            'title' => 'required|string',
            'message' => 'required|string|max:2000',
        ];
    }
}
