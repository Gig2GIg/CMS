<?php

namespace Tests\Unit;

use App\Models\MarketplaceCategory;
use App\Models\Marketplace;
use Tests\TestCase;

use App\Http\Repositories\Marketplace\MarketplaceRepository;

class MarketplaceControllerTest extends TestCase
{

    protected $marketplace_category_id;

    public function test_search_marketplace_by_category_by_title_201()
    {
        $marketplaceCategory = factory(MarketplaceCategory::class)->create();

        $data = [
            'address' => $this->faker->address,
            'title' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'services' => $this->faker->realText($maxNbChars = 200, $indexSize = 2),
            'marketplace_category_id' => $marketplaceCategory->id
        ];

        $marketplace_repo = new MarketplaceRepository(new Marketplace());
      
        $marketplace = $marketplace_repo->create($data);
       
        $value = $marketplace->title;
       

        $response = $this->json('GET',
            'api/a/marketplace_categories/'. $marketplaceCategory->id.'/marketplaces/search?value='. $value.'&token=' . $this->token2,
            $data);
       
        $response->assertStatus(200);
    }
}
