<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserManager;
use App\Models\UserSlots;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class AuditionManagementTest extends TestCase
{
    protected $userId;
    protected $auditionId;
    protected $rolId;
    protected $token;



    public function test_save_upcoming_audition_avaliable()
    {
        $audition = factory(Auditions::class)->create(['user_id' => $this->userId]);
        $users = factory(User::class, 7)->create();
        $appointment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id'=>$appointment->id]);
        factory(Appointments::class, 10)->create(['auditions_id' => $audition->id]);
        $users->each(function ($item) use ($audition, $appointment) {
            factory(UserSlots::class)->create([
                'user_id' => $item->id,
                'appointment_id' => $appointment->id,
                'status' => 'reserved',
                'slots_id' => factory(Slots::class)->create(['appointment_id' => $appointment->id])->id

            ]);
        });

        $response = $this->json('POST',
            'api/a/auditions/user?token=' . $this->token,
            [
                'appointment' => $appointment->id,
                'rol' => factory(Roles::class)->create(['auditions_id' => $audition->id])->id,
                'type' => 1
            ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);
    }
    public function test_save_upcoming_audition_avaliable_online()
    {
        $audition = factory(Auditions::class)->create(['user_id' => $this->userId]);
        $appointment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $response = $this->json('POST',
            'api/a/auditions/user?token=' . $this->token,
            [
                'appointment' => $appointment->id,
                'rol' => factory(Roles::class)->create(['auditions_id' => $audition->id])->id,
                'type' => 1,
                'online'=>true,
            ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);
    }

    public function test_save_upcoming_audition_not_avaliable()
    {
        $audition = factory(Auditions::class)->create(['user_id' => $this->userId]);
        $users = factory(User::class, 7)->create();
        $appointment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        factory(Appointments::class, 10)->create(['auditions_id' => $audition->id]);
        $users->each(function ($item) use ($audition, $appointment) {
            factory(UserSlots::class)->create([
                'user_id' => $item->id,
                'appointment_id' => $appointment->id,
                'status' => 'reserved',
                'slots_id' => factory(Slots::class)->create(['appointment_id' => $appointment->id])->id

            ]);
        });
        $response = $this->json('POST',
            'api/a/auditions/user?token=' . $this->token,
            [
                'appointment' => $appointment->id,
                'rol' => $this->rolId,
                'type' => 1
            ]);
        $response->assertStatus(406);
        $response->assertJsonStructure(['error']);
    }



    public function test_update_requested_to_upcommig()
    {

        $appoinment = factory(Appointments::class)->create(['auditions_id' => $this->auditionId]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinment->id]);
        $data = factory(UserAuditions::class)->create([
            'user_id' => $this->userId,
            'rol_id' => $this->rolId,
            'appointment_id' => $appoinment->id,
            'type' => 2,
        ]);
        $response = $this->json('PUT',
            'api/a/auditions/user/update/' . $data->id . '?token=' . $this->token,
            [
                'slot' => [
                    'slot' => $slot->id,
                    'appointment' => $appoinment->id,
                    'rol' => $data->rol_id
                ]
            ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function test_update_requested_to_upcommig_not_slot()
    {

        $appoinment = factory(Appointments::class)->create(['auditions_id' => $this->auditionId]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinment->id]);
        $data = factory(UserAuditions::class)->create([
            'user_id' => $this->userId,
            'rol_id' => $this->rolId,
            'appointment_id' => $appoinment->id,
            'type' => 2,
        ]);
        $response = $this->json('PUT',
            'api/a/auditions/user/update/' . $data->id . '?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function test_save_upcoming_audition_error()
    {

        $response = $this->json('POST',
            'api/a/auditions/user?token=' . $this->token,
            [
                'auditions' => 20,
                'rol' => 34,
                'type' => 1
            ]);
        $response->assertStatus(406);
        $response->assertJsonStructure(['error']);
    }

    public function test_auditions_upcomming()
    {
        $appoinmet = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);
        factory(UserAuditions::class, 2)->create([
            'user_id' => $this->userId,
            'rol_id' => $this->rolId,
            'appointment_id' => $appoinmet->id,
            'type' => 1,
        ]);
        factory(UserAuditions::class, 2)->create([
            'user_id' => $this->userId,
            'rol_id' => factory(Roles::class)->create(['auditions_id' => $this->auditionId]),
            'appointment_id' => $appoinmet->id,
            'type' => 1,
            'slot_id' => factory(Slots::class)->create([
                'appointment_id' => factory(Appointments::class)->create([
                    'auditions_id' => $this->auditionId
                ])->id,
            ]),
        ]);
        $fac = factory(Appointments::class)->create([
            'auditions_id' => $this->auditionId,
            'status'=>false
        ]);
        factory(UserAuditions::class)->create([
            'user_id' => $this->userId,
            'rol_id' => factory(Roles::class)->create(['auditions_id' => $this->auditionId]),
            'appointment_id' => $fac->id,
            'type' => 1,
            'slot_id' => factory(Slots::class)->create([
                'appointment_id' => factory(Appointments::class)->create([
                    'auditions_id' => $this->auditionId
                ])->id,
            ]),
        ]);
        $response = $this->json('GET',
            'api/a/auditions/user/upcoming?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'id',
            'title',
            'date',
            'union',
            'contract',
            'production',
            'media',
            'number_roles',
        ]]]);
    }

    public function test_auditions_upcomming_det()
    {
        $appoinmet = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);

        $data = factory(UserAuditions::class)->create([
            'user_id' => $this->userId,
            'rol_id' => $this->rolId,
            'appointment_id' => $appoinmet->id,
            'type' => 1,
        ]);
        $response = $this->json('GET',
            'api/a/auditions/user/upcoming/det/' . $data->id . '?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'user_id',
            'appointment_id',
            'auditions_id',
            'title',
            'date',
            'time',
        ]]);
    }

    public function test_auditions_request()
    {
        $appoinmet = factory(Appointments::class)->create([
            'auditions_id'=>$this->auditionId
        ]);


        factory(UserAuditions::class, 5)->create([
            'user_id' => $this->userId,
            'rol_id' => factory(Roles::class)->create(['auditions_id' => $this->auditionId]),
            'appointment_id' => $appoinmet->id,
            'type' => 2,

        ]);
        factory(UserAuditions::class, 5)->create([
            'user_id' => $this->userId,
            'rol_id' => factory(Roles::class)->create(['auditions_id' => $this->auditionId]),
            'appointment_id' => $appoinmet->id,
            'type' => 2,
            'slot_id' => factory(Slots::class)->create([
                'appointment_id' => factory(Appointments::class)->create([
                    'auditions_id' => $this->auditionId
                ])->id,
            ]),
        ]);
        $response = $this->json('GET',
            'api/a/auditions/user/requested?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'id',
            'title',
            'date',
            'union',
            'contract',
            'production',
            'media',
            'number_roles',
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
        $audition->media()->create(['url' => $this->faker->url, 'type' => 6, 'name' => 'test']);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id
        ]);
        $this->rolId = $rol->id;
        $this->userId = $user->id;
        $this->auditionId = $audition->id;
    }


    public function test_it_user_performance_bannend_audition200()
    {

        $response = $this->json('POST',
            'api/a/auditions/banned?token=' . $this->token,
            [
                'audition_id' => $this->auditionId
            ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }


}
