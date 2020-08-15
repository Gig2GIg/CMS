<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvitedUserResource extends JsonResource
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
            'details' => $this->details,
            'email' => $this->email,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'is_premium' => $this->is_premium,
            'is_profile_completed' => $this->is_profile_completed,
            'is_invited' => $this->invited_by ? true : false,
        ];
    }
}
