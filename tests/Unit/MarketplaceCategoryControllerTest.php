<?php

namespace Tests\Unit;

use App\Models\MarketplaceCategory;
use Tests\TestCase;


class MarketplaceCategoryControllerTest extends TestCase
{
   
    public function test_all_marketplace_category_200()
    {
        $marketplaceCategory = factory(MarketplaceCategory::class, 5)->create();

        $response = $this->json('GET',
            'api/a/marketplace_categories'. '?token=' . $this->token2);
        $response->assertStatus(200);

    }

}

