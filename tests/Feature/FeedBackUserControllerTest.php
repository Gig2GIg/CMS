<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserSlots;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeedBackUserControllerTest extends TestCase
{
    protected $userId;
    protected $auditionId;
    protected $rolId;
    protected $token;



    public function test_get_feedback_final(){
        $user = factory(User::class)->create();
        $userDeta = factory(UserDetails::class)->create([
            'user_id'=>$user->id
        ]);
        $audition = factory(Auditions::class)->create([
            'user_id'=>$user->id,
        ]);
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$audition->id
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);
        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'appointment_id'=>$appoinment->id,
            'slots_id'=>$slot->id
        ]);
        $work = [
            'vocals',
            'acting',
            'dancing',
        ];
        $feed = factory(Feedbacks::class)->create([
            'appointment_id'=>$appoinment->id,
            'user_id' => $this->userId, //id usuario que recibe evaluacion
            'evaluator_id' =>$user->id, //id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'slot_id'=>$slot->id,
            'favorite' => $this->faker->boolean()
        ]);

        $response = $this->json('GET','api/a/feedbacks/final/'.$appoinment->id.'?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'auditions_id',
            'user_id',
            'evaluator_id',
            'evaluation',
            'callback',
            'work',
            'favorite'
        ]]);

    }

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create([
                'email' => 'token@test.com',
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;
        $user->image()->create(['url' => $this->faker->url, 'name' => 'test']);
        $userDetails = factory(UserDetails::class)->create([
            'type' => 2,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token');

        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);
        $audition->media()->create(['url' => $this->faker->url, 'type' => 4, 'name' => 'test']);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id
        ]);
        $this->rolId = $rol->id;
        $this->userId = $user->id;
        $this->auditionId = $audition->id;
    }
}
