<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name'=> $this->title,
            'address'=>$this->address,
            'phone_number'=>$this->phone_number,
            'services'=>$this->services, 
            'image' => $this->image
        ];
    }
}
