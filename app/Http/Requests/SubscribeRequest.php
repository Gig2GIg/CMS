<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends ApiRequest
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
            'user_id' => ['required'],
            'stripe_plan_id'=> ['required'],
            'stripe_plan_name'=> ['required'],
            'number'=> ['required'],
            'exp_month'=> ['required'],
            'cvc'=> ['required'],
            'exp_year'=> ['required'],
            // 'address'=> ['required'],
            // 'city'=> ['required'],
            // 'state'=> ['required'],
            // 'zip'=> ['required'],
        ];
    }
}
