<?php

namespace Tests\Unit;

use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Roles;
use App\Models\User;
use App\Models\UserDetails;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeedBackControllerTest extends TestCase
{
    protected $userId;
    protected $auditionId;
    protected $rolId;
    protected $token;

    public function test_set_feedback_to_performer()
    {
        $user = factory(User::class)->create();
        $work = [
            'vocals',
            'acting',
            'dancing',
        ];
        $response = $this->json('POST', 'api/t/feedbacks/add?token=' . $this->token, [
            'auditions' => $this->auditionId,
            'user' => $user->id, //id usuario que recibe evaluacion
            'evaluator' => $this->userId,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
        ]);

        $response->assertStatus(201);
        $response->assertJson(['data' => 'Feedback add']);
    }

    public function test_list_feedbacks_by_audition()
    {

        $user = factory(User::class)->create();
        factory(Feedbacks::class,10)->create([
            'user_id'=>$user->id,
            'auditions_id'=>$this->auditionId,
            'evaluator_id'=>$this->userId
        ]);
        $response = $this->json('GET', 'api/t/feedbacks/list?token=' . $this->token,[
            'audition'=>$this->auditionId,
            'performer'=>$user->id
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'auditions_id',
            'user_id',
            'evaluator_id',
            'evaluation',
            'callback',
            'work',
            'favorite'
        ]]]);
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
            'type' => 1,
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
