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
            'is_active' => $this->is_active,
            'is_premium' => $this->is_premium,
            'stripe_id' => $this->stripe_id,
            'card_brand' => $this->card_brand,
            'card_last_four' => $this->card_last_four,
        ];
    }
}
