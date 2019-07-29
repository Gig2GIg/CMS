<?php

namespace Tests\Unit;


use App\Models\User;
use App\Models\UserDetails;

use App\Models\Notifications\NotificationHistory;
use Tests\TestCase;


class NotificationControllerTest extends TestCase
{
    protected $token;
    protected $token_performance;
    protected $testId;
    protected $performanceId;
    protected $skillId;

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
        
        $this->token = $response->json('access_token');
        
        // USER PERFORMANCE
        $user_performance = factory(User::class)->create([
                'email' => 'performance@test.com',
                'password' => bcrypt('123456')]
        );
        $this->performanceId = $user_performance->id;
        $user_performance->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $user_performanceDetails = factory(UserDetails::class)->create([
            'type' => 2,
            'user_id' => $user_performance->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'performance@test.com',
            'password' => '123456',
        ]);
        
        $this->token_performance = $response->json('access_token');
        

    }

    public function test_all_notification_history_200()
    {

        $data = factory(NotificationHistory::class, 6)->create(['user_id'=> $this->testId]);
        $response = $this->json('GET', 'api/t/notification-history?token=' . $this->token);

        $response->assertStatus(200);
        $dataj = json_decode($response->content(), true);
        $count = count($dataj['data']);
        $this->assertTrue($count > 5);
        $response->assertJsonStructure(['data' => [[
            "id"
        ]]]);
    }

    public function test_delete_history_director_200()
    {
        $data = factory(NotificationHistory::class)->create(['user_id'=> $this->testId]);

        $response = $this->json('DELETE','api/t/notification-history/delete/'.$data->id.'?token='.$this->token);
        $response->assertStatus(200);

    }

    public function test_delete_history_performance200()
    {
        $data = factory(NotificationHistory::class)->create(['user_id'=> $this->performanceId]);

        $response = $this->json('DELETE','api/a/notification-history/delete/'.$data->id.'?token='.$this->token_performance);
        $response->assertStatus(200);

    }
}
