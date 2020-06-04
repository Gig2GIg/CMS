<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InAppSuccessRequest extends ApiRequest
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
            'name'=> ['required'],
            'product_id'=> ['required'],
            'original_transaction'=> [''],
            'current_transaction'=> ['required'],
            'ends_at'=> [''],
            'purchase_platform'=> ['required', Rule::in(['web', 'android', 'ios'])],
            'purchased_at' => ['required'],
            'transaction_receipt' => [''],
        ];
    }
}
