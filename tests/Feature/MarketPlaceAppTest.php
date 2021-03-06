<?php

namespace Tests\Feature;

use App\Models\MarketplaceCategory;
use App\Models\User;
use App\Models\UserDetails;
use Tests\TestCase;


class MarketPlaceAppTest extends TestCase
{
    protected $token;
    protected $testId;


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
            'type' => 2,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token');

    }


    public function test_create_marketplace_201()
    {


        $data = [
            'address' => 'sdsdsdsdsd',
            'title' => 'sdsdsdsd',
            'phone_number' => '343434343434',
            'email' => $this->faker->safeEmail(),
            'services' => 'wwewewewewe',
            'image_name' =>  'Some',
            'image_url'=>  'https://stackoverflow.com/questions/30878105/laravel-5-form-request-validation-returning-forbidden-error',
            'url_web' => $this->faker->url
        ];
        factory(MarketplaceCategory::class)->create();
        $response = $this->json('POST',
            'api/a/marketplaces/create?token=' . $this->token,
            $data);

        $response->assertStatus(201);
    }

}
