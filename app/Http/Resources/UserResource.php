<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Performers;

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
        
        $return = [
            'id' => $this->id,
            'email' => $this->email,
            'image' => $this->image,
            'details' => $this->details,
            'billing_details' => $this->billingDetails,
            'union_members' => $this->memberunions,
            'subscription'=> $this->subscriptions()->first(),
            'is_active' => $this->is_active,
            'is_premium' => $this->is_premium,
            'is_invited' => $this->invited_by ? true : false,
            'stripe_id' => $this->stripe_id,
            'card_brand' => $this->card_brand,
            'card_last_four' => $this->card_last_four,
            'admin_id' => $this->invited_by,
            'on_grace_period' => $this->subscriptions()->first()->onGracePeriod()
        ];

        if($this->details->type == 1){
            $repo = new Performers();
            $return['total_performers'] = $repo->where('director_id', $this->id)->get()->count();
        }

        return $return; 
    }
}
