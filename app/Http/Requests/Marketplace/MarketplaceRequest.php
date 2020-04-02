<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class MarketplaceRequest extends FormRequest
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
            // 'address' => 'required|string|max:50',
            'title' => 'required|string|max:50',
            // 'email' => 'required|string|email',
            'services' => 'required|string|max:100',
            // 'phone_number' => 'required|string|max:20',
            'image_url' => 'required|string',
            'image_name' => 'required|string',
            // 'url_web' => 'required|string'
        ];
    }
}
