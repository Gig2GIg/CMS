<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Performers;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserSlots;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeedBackControllerTest extends TestCase
{
    protected $userId;
    protected $auditionId;
    protected $rolId;
    protected $token;

    public function test_set_feedback_director_to_performer()
    {
        $user = factory(User::class)->create();
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);
        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'auditions_id'=>$this->auditionId,
            'slots_id'=>$slot->id
        ]);
        $work = [
            'vocals',
            'acting',
            'dancing',
        ];
        $response = $this->json('POST', 'api/t/feedbacks/add?token=' . $this->token, [
            'auditions' => $this->auditionId,//$this->auditionId,
            'user' => $user->id,//$user->id, //id usuario que recibe evaluacion
            'evaluator' => $this->userId, //$this->userId,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'slot_id'=>$slot->id,
            'comment' => $this->faker->text()
        ]);


        $response->assertStatus(201);
        $response->assertJson(['data' => 'Feedback add']);
        $response->assertJsonStructure([
            'data',
            'feedback_id'
        ]);
    }


    public function test_update_feedback_director_to_performer()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);

        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'auditions_id'=>$this->auditionId,
            'slots_id'=>$slot->id
        ]);

        $work = [
            'vocals',
            'acting',
            'dancing',
        ];

        $feedback = factory(Feedbacks::class)->create([
            'auditions_id' => $this->auditionId,//$this->auditionId,
            'user_id' => $user->id,//$user->id, //id usuario que recibe evaluacion
            'evaluator_id' => $user2->id, //$this->userId,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'slot_id'=>$slot->id,
            'comment' => $this->faker->text()
        ]);

        $response= $this->json('PUT', 'api/t/auditions/'.$this->auditionId  .'/feedbacks/update?token=' . $this->token, [
            'user_id' => $user->id,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'comment' => $this->faker->text()
        ]);


        $response->assertStatus(200);
        $response->assertJson(['data' => 'Feedback update']);

    }

    public function test_show_feedback_details_director_to_performer()
    {
        $user = factory(User::class)->create();
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);

        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'auditions_id'=>$this->auditionId,
            'slots_id'=>$slot->id
        ]);

        $work = [
            'vocals',
            'acting',
            'dancing',
        ];

        $feedback = factory(Feedbacks::class)->create([
            'auditions_id' => $this->auditionId,//$this->auditionId,
            'user_id' => $user->id,//$user->id, //id usuario que recibe evaluacion
            'evaluator_id' => $this->userId, //$this->userId,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'slot_id'=>$slot->id,
            'comment' => $this->faker->text()
        ]);

        $response= $this->json('GET', 'api/t/auditions/'. $this->auditionId .'/feedbacks/details?user_id='.$user->id .'&token=' . $this->token);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'id',
            'evaluation',
            'callback',
            'work',
            'favorite',
            'comment',
        ]]);

    }



    public function test_show_feedback_details_director_to_performer_404()
    {
        $user = factory(User::class)->create();
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);

        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'auditions_id'=>$this->auditionId,
            'slots_id'=>$slot->id
        ]);

        $work = [
            'vocals',
            'acting',
            'dancing',
        ];

        $feedback = factory(Feedbacks::class)->create([
            'auditions_id' => $this->auditionId,//$this->auditionId,
            'user_id' => $user->id,//$user->id, //id usuario que recibe evaluacion
            'evaluator_id' => $this->userId, //$this->userId,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'slot_id'=>$slot->id,
            'comment' => $this->faker->text()
        ]);

        $response= $this->json('GET', 'api/t/auditions/'. $this->auditionId .'/feedbacks/details?user_id='. '3000' .'&token=' . $this->token);

        $response->assertStatus(404);


    }



    public function test_set_feedback_contributor_to_performer()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);
        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'auditions_id'=>$this->auditionId,
            'slots_id'=>$slot->id
        ]);
        $work = [
            'vocals',
            'acting',
            'dancing',
        ];
        $response = $this->json('POST', 'api/t/feedbacks/add?token=' . $this->token, [
            'auditions' => $this->auditionId,
            'user' => $user->id, //id usuario que recibe evaluacion
            'evaluator' => $user2->id,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'comment' => $this->faker->text(),
            'slot_id'=>$slot->id
        ]);

        $response->assertStatus(201);
        $response->assertJson(['data' => 'Feedback add']);
    }
    public function test_set_feedback_contributor_to_performer_not_add_performer_feedback()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);
        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'auditions_id'=>$this->auditionId,
            'slots_id'=>$slot->id
        ]);
        $work = [
            'vocals',
            'acting',
            'dancing',
        ];
        factory(Performers::class)->create([
            'performer_id' => $user->id,
            'director_id' => $this->userId,
            'uuid' => Str::uuid()->toString(),
        ]);
        $response = $this->json('POST', 'api/t/feedbacks/add?token=' . $this->token, [
            'auditions' => $this->auditionId,
            'user' => $user->id, //id usuario que recibe evaluacion
            'evaluator' => $user2->id,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'comment' => $this->faker->text(),
            'slot_id'=>$slot->id
        ]);

        $response->assertStatus(201);
        $response->assertJson(['data' => 'Feedback add']);
    }

    public function test_list_feedbacks_by_audition()
    {

        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id'=>$user->id
        ]);
        $appointment = factory(Appointments::class)->create([
            'auditions_id'=>$audition->id,
        ]);
        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appointment->id
        ]);
        factory(Feedbacks::class)->create([
            'user_id'=>$user->id,
            'auditions_id'=>$this->auditionId,
            'evaluator_id'=>$this->userId,
            'slot_id'=>$slot->id,
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

        public function test_set_feedback_contributor_to_performer_406()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appoinment->id
        ]);
        $slot_user = factory(UserSlots::class)->create([
            'user_id'=>$this->userId,
            'auditions_id'=>$this->auditionId,
            'slots_id'=>$slot->id
        ]);
        $feedback =  factory(Feedbacks::class)->create([
            'auditions_id' => $this->auditionId,
            'user_id' => $user->id, //id usuario que recibe evaluacion
            'evaluator_id' => $user2->id,//id de usuario que da feecback,
               'slot_id'=>$slot->id
        ]);
        $work = [
            'vocals',
            'acting',
            'dancing',
        ];
        $response = $this->json('POST', 'api/t/feedbacks/add?token=' . $this->token, [
            'auditions' => $this->auditionId,
            'user' => $user->id, //id usuario que recibe evaluacion
            'evaluator' => $user2->id,//id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
             'comment' => $this->faker->text(),
            'slot_id'=>$slot->id
        ]);

        $response->assertStatus(406);
        $response->assertJson(['data' => 'Feedback not add']);
    }
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create([
                'email' => 'token5@test.com',
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;
        $user->image()->create(['url' => $this->faker->url, 'name' => 'test']);
        $userDetails = factory(UserDetails::class)->create([
            'type' => 1,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token5@test.com',
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
