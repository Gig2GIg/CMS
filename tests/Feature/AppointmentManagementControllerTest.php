<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserSlots;
use Tests\TestCase;


class AppointmentManagementControllerTest extends TestCase
{
    protected $testId;
    protected $token;
    protected $userId;
    protected $auditionId;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create([
                'email' => 'token@test.com',
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;

        $userDetails = factory(UserDetails::class)->create([
            'type' => 1,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token');


        // CREATED AUDITIONS WITH USER TYPE TABLE
        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);

        $this->auditionId = $audition->id;
    }


    public function test_get_rounds_from_audition_200(){
        factory(Appointments::class)->create(['auditions_id' => $this->auditionId, 'round' => 1]);
        factory(Appointments::class)->create(['auditions_id' => $this->auditionId, 'round' => 2]);
        factory(Appointments::class)->create(['auditions_id' => $this->auditionId, 'round' => 3]);
        factory(Appointments::class)->create(['auditions_id' => $this->auditionId, 'round' => 4]);
        $response = $this->json('GET',
            'api/t/appointment/'.$this->auditionId.'/rounds?token=' . $this->token);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'=>[[
                'id',
                'round',
                'status'
            ]]
        ]);
    }
    public function test_get_rounds_from_audition_404(){
        $response = $this->json('GET',
            'api/t/appointment/'.$this->auditionId.'/rounds?token=' . $this->token);
        $response->assertStatus(404);
        $response->assertJson([
            'data'=>[]
        ]);
    }

    public function test_create_new_round_in_appointment_200(){

        $data = [
            'date' => '10-20-2019',
            'time' => '10:00',
            'location' => json_encode([
                "latitude"=> $this->faker->latitude,
                "latitudeDelta"=> $this->faker->latitude,
                "longitude"=>$this->faker->longitude,
                "longitudeDelta"=>$this->faker->longitude,
            ]),
            'number_slots'=>10,
            'type'=>1,
            'length'=>10,
            'start'=>'10:00',
            'end'=>'12:00',
            'round'=>2,
            'status'=>true,
            'slots' => [
                [
                    'time' => $this->faker->time('i'),
                    'status' => $this->faker->boolean(),
                    'is_walk' => $this->faker->boolean()
                ],
                [
                    'time' => $this->faker->time('i'),
                    'status' => $this->faker->boolean(),
                    'is_walk' => $this->faker->boolean()
                ],
                [
                    'time' => $this->faker->time('i'),
                    'status' => $this->faker->boolean(),
                    'is_walk' => $this->faker->boolean()
                ],
                [
                    'time' => $this->faker->time('i'),
                    'status' => $this->faker->boolean(),
                    'is_walk' => $this->faker->boolean()
                ]
            ]
        ];
        $response = $this->json('POST',
            'api/t/appointment/'.$this->auditionId.'/rounds?token=' . $this->token,$data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data'=>[
                'id',
                'round',
                'status'
            ]
        ]);
    }

    public function test_create_new_round_in_appointment_406(){
        $data = [
            'number_slots'=>10,
            'date' => '10-20-2019',
            'time' => '10:00',
            'type'=>1,
            'length'=>10,
            'location' => json_encode([
                "latitude"=> $this->faker->latitude,
                "latitudeDelta"=> $this->faker->latitude,
                "longitude"=>$this->faker->longitude,
                "longitudeDelta"=>$this->faker->longitude,
            ]),
            'start'=>'10:00',
            'end'=>'12:00',
            'round'=>2,
            'status'=>true,
            'auditions_id'=>$this->auditionId
        ];
        $response = $this->json('POST',
            'api/t/appointment/'.$this->auditionId.'/rounds?token=' . $this->token,$data);

        $response->assertStatus(406);
        $response->assertJsonStructure([
            'message',
            'data'=>[

            ]
        ]);
    }
    public function test_close_round_in_appointment(){

        $appoinment=factory(Appointments::class)->create(['auditions_id' => $this->auditionId, 'round' => 1]);


        $data=[
            'status'=>false
        ];
        $response = $this->json('PUT',
            'api/t/appointment/'.$appoinment->id.'?token=' . $this->token,$data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data'=>[

            ]
        ]);
    }

    public function test_get_all_slots_by_appointment_200(){
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id'=>$user->id
        ]);

        $appointment = factory(Appointments::class)->create([
            'auditions_id'=>$audition->id
        ]);
        factory(Slots::class,10)->create(
            ['appointment_id'=>$appointment->id]
        );
        $response = $this->json('GET','api/t/appointments/'.$appointment->id.'/slots?token='.$this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data'=>[
                [
                    'id',
                    'time'
                ]

            ]]);
    }
}
