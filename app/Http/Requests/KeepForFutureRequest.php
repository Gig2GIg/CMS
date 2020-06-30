<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KeepForFutureRequest extends FormRequest
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
            'slot_id' => 'required',
            'user' => 'required',
            'evaluator' => 'required',
            'appointment_id' => 'required'
        ];    
        
    }
}
