<?php

namespace App\Http\Resources;


use App\Http\Repositories\Marketplace\MarketplaceRepository;

use App\Models\Marketplace;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationMarketplacesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
       
        $marketplaceRepo = new MarketplaceRepository(new Marketplace());
        $markeplace = $marketplaceRepo->find($this->marketplace_id);
        return [
            'id' => $this->id,
            'markeplace' => $this->markeplace
        ];
    }
}
