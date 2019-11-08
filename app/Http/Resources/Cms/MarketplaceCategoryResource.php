<?php

namespace App\Http\Resources\Cms;

use App\Http\Repositories\Marketplace\MarketplaceRepository;
use App\Models\Marketplace;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceCategoryResource extends JsonResource
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
            'name'=> $this->name,
            'description'=>$this->description,
    ];
    }
}
