<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\RecommendationsRepository;
use App\Models\Recommendations;
use Tests\TestCase;

use App\Models\User;
use App\Models\UserDetails;

use App\Models\Marketplace;
use App\Models\Auditions;

class RecommendationsUnitTest extends TestCase
{
    protected $marketplace_id;
    protected $user_id;
    protected $audition_id;

 
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
    
    public function test_create_recommendations()
    {

       
        $data = [
            'marketplace_id' => $this->marketplace_id,
            'user_id' =>  $this->user_id,
            'audition_id' => $this->audition_id
        ];
        $repo = new RecommendationsRepository(New Recommendations());
        $recommendations = $repo->create($data);
 

        $this->assertInstanceOf(Recommendations::class, $recommendations);
        $this->assertEquals($data['user_id'], $recommendations->user_id);
        $this->assertEquals($data['audition_id'], $recommendations->audition_id);

    }
    
    public function test_create_recommendations_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new RecommendationsRepository(New Recommendations());
        $recommendations = $repo->create([]);
        $this->assertInstanceOf(Recommendations::class, $recommendations);
    }

    public function test_recommendations_get_all(){
        $data = [
            'marketplace_id' => $this->marketplace_id,
            'user_id' =>  $this->user_id,
            'audition_id' => $this->audition_id
        ];

        factory(Recommendations::class, 5)->create($data);
        $recommendations = new RecommendationsRepository(new Recommendations());
        $data = $recommendations->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_show_recommendations()
    {
        $data = [
            'marketplace_id' => $this->marketplace_id,
            'user_id' =>  $this->user_id,
            'audition_id' => $this->audition_id
        ];

        $recommendations = factory(Recommendations::class)->create($data);
        $repo = new RecommendationsRepository(new Recommendations());
        $found =  $repo->find($recommendations->id);
        $this->assertInstanceOf(Recommendations::class, $found);
        $this->assertEquals($found->name,$recommendations->name);
        $this->assertEquals($found->rol,$recommendations->rol);
    }


    public function test_delete_recommendations()
    {
        $data = [
            'marketplace_id' => $this->marketplace_id,
            'user_id' =>  $this->user_id,
            'audition_id' => $this->audition_id
        ];
        $recommendations = factory(Recommendations::class)->create($data);
        $repo = new RecommendationsRepository($recommendations);
        $delete = $repo->delete();
        $this->assertTrue($delete);
    }

    public function test_show_recommendations_exception()
    {
        $this->expectException(NotFoundException::class);
        $repo = new RecommendationsRepository(new Recommendations());
        $repo->find(28374);
    }


    public function test_recommendations_delete_null()
    {
        $repo = new RecommendationsRepository(new Recommendations());
        $delete = $repo->delete();
        $this->assertNull($delete);

    }


}
