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
      if(isset($this->stripe_id)){
          $card = $this->defaultCard();
      }else{
          $card =[];
      }

        return [
            'id' => $this->id,
            'email' => $this->email,
            'image' => $this->image,
            'details' =>$this->details,
            'union_members' => $this->memberunions,
            'subscription'=>$this->subscriptions,
            'card'=>$card,
        ];
    }
}
