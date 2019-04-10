<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'term_of_use' => $this->term_of_use,
            'privacy_policy' => $this->privacy_policy,
            'app_info' => $this->app_info,
            'contact_us' => $this->contact_us
        ];
    }
}
