<?php

namespace Tests\Unit;

use App\Models\MarketplaceCategory;
use App\Models\Marketplace;
use Tests\TestCase;


class MarketplaceControllerTest extends TestCase
{

    protected $marketplace_category_id;

    public function test_create_marketplace_201()
    {
        $data = [
            'address' => 'addres adress adress',
            'title' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'services' => $this->faker->text($maxNbChars = 100),
            'image_url' => "https://stackoverflow.com/questions/30878105/laravel-5-form-request-validation-returning-forbidden-error"
        ];

        $marketplaceCategory = factory(MarketplaceCategory::class)->create();
        $response = $this->json('POST',
            'api/cms/marketplace_categories/'. $marketplaceCategory->id.'/marketplaces/create?token=' . $this->token3,
            $data);
        
        $response->assertStatus(201);
    }

    public function test_create_marketplace_422()
    {
        $response = $this->json('POST',
            'api/cms/marketplace_categories/create?token=' . $this->token3,
            []);
        $response->assertStatus(422);

    }

    public function test_show_marketplace_200()
    {
        $marketplaceCategory = factory(Marketplace::class)->create();

        $response = $this->json('GET',
        'api/cms/marketplaces/show/' . $marketplaceCategory->id . '?token=' . $this->token3);
        $response->assertStatus(200);
    }
   
    public function test_show_all_marketplace_200()
    {
        $marketplaceCategory = factory(Marketplace::class, 10)->create();

        $response = $this->json('GET',
            'api/cms/marketplaces?token=' . $this->token3);
        $response->assertStatus(200);
    }

    public function test_show_all_marketplace_404()
    {
        $response = $this->json('GET',
            'api/cms/marketplaces?token=' . $this->token3);
        $response->assertStatus(404);
    }

    public function test_show_marketplace_404()
    {
        $id = 20239;

        $response = $this->json('GET',
            'api/cms/marketplaces/show/' . $id . '?token=' . $this->token);
        $response->assertStatus(404);
    }

    public function test_update_marketplace_200()
    {
        $data = [
            'address' => 'addres adress adress',
            'title' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'services' => $this->faker->realText($maxNbChars = 200, $indexSize = 2)
        ];

        $marketplace = factory(Marketplace::class)->create();

        $response = $this->json('PUT',
            'api/cms/marketplaces/update/' .$marketplace->id.'?token=' . $this->token3,
            $data);

        $response->assertStatus(204);

    }

}
