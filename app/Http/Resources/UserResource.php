<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Performers;
use App\Models\User;

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
            //'billing_details' => $this->billingDetails,
            'union_members' => $this->memberunions,
            'subscription'=> $this->subscriptions()->first(),
            'is_active' => $this->is_active,
            'is_premium' => $this->is_premium,
            'is_profile_completed' => $this->is_profile_completed,
            'is_invited' => $this->invited_by ? true : false,
            'stripe_id' => $this->stripe_id,
            'card_brand' => $this->card_brand,
            'card_last_four' => $this->card_last_four,
            'admin_id' => $this->invited_by,
        ];

        if($this->details->type == 1){
            $repo = new Performers();           
            $userRepo = new User();
            
            //it is to fetch logged in user's invited users data if any
            $invitedUserIds = $userRepo->where('invited_by', $this->id)->get()->pluck('id');

            //pushing own ID into WHERE IN constraint
            $invitedUserIds->push($this->id); 

            $return['total_performers'] = $repo->whereIn('director_id', $invitedUserIds->unique()->values())->get()->count(); 

            $return['on_grace_period'] = $this->subscriptions()->first() && $this->subscriptions()->first()->grace_period ? true : false;
        }

        return $return; 
    }
}
