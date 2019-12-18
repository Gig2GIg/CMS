<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FeaturedListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $date = Carbon::parse( $this->created_at, 'UTC');
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'email' =>$this->email,
            'created_at' =>  $date->isoFormat('MMM Do YY')
        ];
    }
}
