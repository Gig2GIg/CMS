<?php

namespace Tests\Unit\Marketplace;

use Tests\TestCase;
use App\Http\Repositories\CalendarRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Calendar;
use App\Models\User;
use App\Models\UserDetails;

class CalendarControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    protected $token;
    protected $user_id;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create([
                'email' => 'token@test.com',
                'password' => bcrypt('123456')]
        );
        $this->user_id = $user->id;
        $user->image()->create(['url' => $this->faker->url]);
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

    public function test_create_event_201()
    {
        $data = [
            'production_type' => $this->faker->name,
            'project_name' => $this->faker->name,
            'start_date' => '04-15',
            'end_date' => '04-19',
            'user_id' => $this->user_id
        ];

        $response = $this->json('POST',
            'api/a/calendar/create_event?token=' . $this->token,
            $data);
        
        $response->assertStatus(201);
    }

    public function test_create_event_when_start_date_is_occupied_422()
    {

        $calendar = factory(Calendar::class)->create();

        $data = [
            'production_type' => $this->faker->name,
            'project_name' => $this->faker->name,
            'start_date' => '04-20',
            'end_date' => '04-30',
            'user_id' => $this->user_id
        ];

        $response = $this->json('POST',
            'api/a/calendar/create_event?token=' . $this->token,
            $data);
        
        $response->assertStatus(422);
    }

    public function test_create_event_when_end_date_is_occupied_422()
    {

        $calendar = factory(Calendar::class)->create();

        $data = [
            'production_type' => $this->faker->name,
            'project_name' => $this->faker->name,
            'start_date' => '04-10',
            'end_date' => '04-27',
            'user_id' => $this->user_id
        ];

        $response = $this->json('POST',
            'api/a/calendar/create_event?token=' . $this->token,
            $data);
        
        $response->assertStatus(422);
    }

    public function test_create_event_422()
    {
        $response = $this->json('POST',
            'api/a/calendar/create_event?token=' . $this->token,
            []);
        $response->assertStatus(422);

    }

    public function test_show_event_200()
    {
        $calendar = factory(Calendar::class)->create();

        $response = $this->json('GET',
        'api/a/calendar/show/' . $calendar->id . '?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function test_show_event_404()
    {
        $id = 1212;

        $response = $this->json('GET',
            'api/a/calendar/show/' . $id . '?token=' . $this->token);
        $response->assertStatus(404);
    }
   
    public function test_show_all_events_200()
    {
        $calendars = factory(Calendar::class, 10)->create();

        $response = $this->json('GET',
            'api/a/calendar/show?token=' . $this->token);

        $response->assertStatus(200);
    }

    public function test_show_all_events_404()
    {
        $response = $this->json('GET',
            'api/a/calendar/show?token=' . $this->token);
        $response->assertStatus(404);
    }

    

    public function test_update_event_200()
    {
        $data = [
            'production_type' => $this->faker->name,
            'project_name' => $this->faker->name,
            'start_date' => '04-03',
            'end_date' => '04-05'
        ];

        $calendar = factory(Calendar::class)->create();

        $response = $this->json('PUT',
            'api/a/calendar/update/' .$calendar->id.'?token=' . $this->token,
            $data);

        $response->assertStatus(200);

    }

    public function test_delete_event_200(){
        $calendar = factory(Calendar::class)->create();

        $response = $this->json('DELETE',
        'api/a/calendar/delete/'.$calendar->id."?token=".$this->token);
        $response->assertStatus(200);
    }

    public function test_delete_event_404(){
        $id = 1212;

        $response = $this->json('DELETE',
        'api/a/calendar/delete/'.$id."?token=".$this->token);
        $response->assertStatus(404);
    }

}
