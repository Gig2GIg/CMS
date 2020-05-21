<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'image' => $this->image,
            'details' => $this->details,
            'billing_details' => $this->billingDetails,
            'union_members' => $this->memberunions,
            'subscription'=> $this->subscriptions,
            'is_active' => $this->is_active
        ];
    }
}
