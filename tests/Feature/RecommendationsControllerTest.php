<?php

namespace Tests\Unit;

use App\Models\Recommendations;
use App\Models\User;
use App\Models\UserDetails;

use App\Models\Marketplace;
use App\Models\Auditions;

use Tests\TestCase;


class RecommendationsControllerTest extends TestCase
{
    protected $token;
    protected $testId;

    protected $marketplace_id;
    protected $user_id;
    protected $audition_id;
    protected $performance_token;
    protected $performanceId;

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
            'type' => 1,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);
        $marketplace = factory(Marketplace::class)->create();
        $this->marketplace_id = $marketplace->id;

        $user2 = factory(User::class)->create();
        $this->user_id = $user2->id;

        $audition = factory(Auditions::class)->create(
            ['user_id' => $user2->id]
        );
        $this->audition_id = $audition->id;

        $this->token = $response->json('access_token');

        // PERFORMANCE USER
        $performance = factory(User::class)->create([
            'email' => 'performance@test.com',
            'password' => bcrypt('123456')]
        );

        $this->performanceId = $performance->id;
        $performance->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $userDetails = factory(UserDetails::class)->create([
            'type' => 2,
            'user_id' => $performance->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'performance@test.com',
            'password' => '123456',
        ]);
       
        $this->performance_token = $response->json('access_token');

    }

    
    public function testCreateRecommendationsMarketplaces201()
    {
        $data = [
            'marketplace_id' => $this->marketplace_id,
            'user_id' => $this->user_id,
            'audition_id' => $this->audition_id,
        ];

        $response = $this->json('POST',
            'api/t/auditions/feeback/recommendations-marketplaces?token=' . $this->token,
            $data);
    
        $response->assertStatus(201);
    }

    public function testRecommendationsMarketplaces200()
    {
        $marketplace2 = factory(Marketplace::class)->create();

        $recomendation = factory(Recommendations::class)->create([
            'marketplace_id' => $marketplace2->id,
            'user_id' => $this->performanceId,
            'audition_id' => $this->audition_id,
        ]);

        $response = $this->json('GET',
            'api/a/auditions/'. $this->audition_id.'/feeback/recommendations-marketplaces?token=' . $this->performance_token);
    
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'id',
            'markeplace'
        ]]]);
    }

    public function testRecommendationsMarketplacesbyUser200()
    {
        $marketplace2 = factory(Marketplace::class)->create();

        $recomendation = factory(Recommendations::class, 10)->create([
            'marketplace_id' => $marketplace2->id,
            'user_id' => $this->performanceId,
            'audition_id' => $this->audition_id,
        ]);

        $response = $this->json('GET',
            'api/t/auditions/'. $this->audition_id.'/feeback/recommendations-marketplaces-by-user?user_id='.$this->performanceId .'&token=' . $this->token);
    
        $response->assertStatus(200);
        dd($response);
        $response->assertJsonStructure(['data' => [[
            'id',
            'markeplace'
        ]]]);
    }


}
