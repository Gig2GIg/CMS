<?php

namespace Tests\Unit;

use App\Models\MarketplaceCategory;
use App\Models\Marketplace;
use App\Models\User;
use App\Models\UserDetails;

use Tests\TestCase;

use App\Http\Repositories\Marketplace\MarketplaceRepository;

class MarketplaceControllerTest extends TestCase
{
    protected $token;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create([
                'email' => 'token@test.com',
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $userDetails = factory(UserDetails::class)->create([
            'type'=>2,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token'); 
    }




    public function test_search_marketplace_by_category_by_title_201()
    {
        $marketplaceCategory = factory(MarketplaceCategory::class)->create();

        $data = [
            'address' => $this->faker->address,
            'title' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'services' => $this->faker->paragraph(),
            'marketplace_category_id' => $marketplaceCategory->id
        ];

        $marketplace_repo = new MarketplaceRepository(new Marketplace());
      
        $marketplace = $marketplace_repo->create($data);
       
        $value = $marketplace->title;
       

        $response = $this->json('GET',
            'api/a/marketplace_categories/'. $marketplaceCategory->id.'/marketplaces/search?value='. $value.'&token=' . $this->token,

            $data);
       
        $response->assertStatus(200);
    }
}
