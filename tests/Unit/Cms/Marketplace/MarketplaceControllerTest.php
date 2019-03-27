<?php

namespace Tests\Unit\Cms\Marketplace;

use App\Models\MarketplaceCategory;
use App\Models\Marketplace;
use Tests\TestCase;
use App\Http\Repositories\Marketplace\MarketplaceRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarketplaceControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_create_marketplace_201()
    {
        $marketplace_category = factory(MarketplaceCategory::class)->create();

        $data = [
            'address' => $this->faker->address,
            'title' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'services' => $this->faker->text($maxNbChars = 100),
            'image_url'=>  'https://stackoverflow.com/questions/30878105/laravel-5-form-request-validation-returning-forbidden-error'
        ];

        $response = $this->json('POST',
            'api/cms/marketplace_categories/'. $marketplace_category->id.'/marketplaces/create?token=' . $this->token3,
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
            'services' => $this->faker->text($maxNbChars = 100)
        ];

        $marketplace = factory(Marketplace::class)->create();

        $response = $this->json('PUT',
            'api/cms/marketplaces/update/' .$marketplace->id.'?token=' . $this->token3,
            $data);

        $response->assertStatus(204);

    }

}
