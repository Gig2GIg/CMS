<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Performers;
use App\Models\CasterTeam;
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
        $selected_admin = NULL;
        $teamFetch = CasterTeam::where(['member_id' => $this->id, 'is_selected' => 1])->first();
        if($teamFetch){
            $admin_id = $selected_admin = $teamFetch->admin_id;
        }else{
            $memberFetch = CasterTeam::where(['member_id' => $this->id])->first();
            if($memberFetch){
                $admin_id = $memberFetch->admin_id;
            } else {
                $admin_id = NULL;
            }
        }

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
            'is_invited' => $admin_id ? true : false,
            'stripe_id' => $this->stripe_id,
            'card_brand' => $this->card_brand,
            'card_last_four' => $this->card_last_four,
            'admin_id' => $admin_id,
            'selected_admin' => $selected_admin
        ];

        if($this->details->type == 1){
            $repo = new Performers();           
            $userRepo = new User();

            //process to performers count
            $fullTeam = array();

            $fullTeam = CasterTeam::where('admin_id', $this->id)->get()->pluck('member_id')->toArray();
            array_push($fullTeam, $this->id);

            $return['total_performers'] = $repo->whereIn('director_id', $fullTeam)->get()->count(); 

            $return['on_grace_period'] = $this->subscriptions()->first() && $this->subscriptions()->first()->grace_period ? true : false;
        }

        return $return; 
    }
}
