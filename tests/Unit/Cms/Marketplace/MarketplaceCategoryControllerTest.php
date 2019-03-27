<?php

namespace Tests\Unit;

use App\Models\MarketplaceCategory;
use Tests\TestCase;


class MarketplaceCategoryControllerTest extends TestCase
{
    public function test_create_marketplace_category_201()
    {
        $data = [
            'name' => $this->faker->words(3, 3),
            'description' => $this->faker->date()
        ];

        $response = $this->json('POST',
            'api/cms/marketplace_categories/create?token=' . $this->token3,
            $data);
            
        $response->assertStatus(201);
    }

    public function test_create_marketplace_category_422()
    {
        $response = $this->json('POST',
            'api/cms/marketplace_categories/create?token=' . $this->token3,
            []);
        $response->assertStatus(422);

    }

    public function test_show_marketplace_category_200()
    {
        $marketplaceCategory = factory(MarketplaceCategory::class)->create();

        $response = $this->json('GET',
        'api/cms/marketplace_categories/show/' . $marketplaceCategory->id . '?token=' . $this->token3);
        $response->assertStatus(200);
    }
   
    public function test_show_all_marketplace_category_200()
    {
        $marketplaceCategory = factory(MarketplaceCategory::class)->create();

        $response = $this->json('GET',
            'api/cms/marketplace_categories?token=' . $this->token3);
        $response->assertStatus(200);
    }

    public function test_show_all_marketplace_category_404()
    {
        $response = $this->json('GET',
            'api/cms/marketplace_categories?token=' . $this->token3);
        $response->assertStatus(404);
    }

    public function test_show_marketplace_category_404()
    {
        $id = 20239;

        $response = $this->json('GET',
            'api/cms/marketplace_categories/show/' . $id . '?token=' . $this->token);
        $response->assertStatus(404);
    }

    public function test_update_marketplace_category_200()
    {
        $data = [
            'name' => $this->faker->words(3, 3),
            'description' => $this->faker->date()
        ];
        $marketplaceCategory = factory(MarketplaceCategory::class)->create();

        $response = $this->json('PUT',
            'api/cms/marketplace_categories/update/' .$marketplaceCategory->id.'?token=' . $this->token,
            $data);
        $response->assertStatus(204);

    }

}

