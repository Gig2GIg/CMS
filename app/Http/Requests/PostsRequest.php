<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostsRequest extends FormRequest
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
            'title' => 'required|string|max:30',
            'body' => 'required|string|max:5000',
            'url_media' => 'string|max:700',
            'type' => 'required|string|max:15',
            'search_to' => 'string|max:15',
            'topic_ids' => 'required',
        ];
    }
}
