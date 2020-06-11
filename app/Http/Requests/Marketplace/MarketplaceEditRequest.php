<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class MarketplaceEditRequest extends FormRequest
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
            // 'address' => 'string|max:50',
            'title' => 'string|max:255',
            // 'email' => 'string|email',
            'services' => 'string',
            // 'phone_number' => 'string|max:20',
            'image_url' => 'nullable|string',
            'image_name' => 'nullable|string',
            'url_web' => 'required|string'
        ];
    }
}
