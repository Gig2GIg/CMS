<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserSlots;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageAppointmentControllerTest extends TestCase
{
    protected $token;

    public function test_set_appoinment_audition()
    {
        $user = factory(User::class)->create();
        $userDet = factory(UserDetails::class)->create(['user_id' => $user->id]);
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $appoinment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinment->id]);
        $rol = factory(Roles::class)->create([
            'auditions_id'=>$audition->id
        ]);
        $useSlot = factory(UserSlots::class)->create([
            'user_id'=>$user->id,
            'auditions_id'=>$audition->id,
            'roles_id'=>$rol->id,
            'status'=>2,

        ]);

        $response = $this->json('POST', 'api/appointments/auditions?token=' . $this->token, [
            'slot' => $slot->id,
            'user' => $user->id,
            'auditions' => $audition->id,
            'rol'=>$rol->id
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'image',
            'name',
            'time'
        ]]);
    }
    public function test_set_appoinment_audition_walk()
    {
        $user = factory(User::class)->create();
        $userDet = factory(UserDetails::class)->create(['user_id' => $user->id]);
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $appoinment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinment->id]);
        $rol = factory(Roles::class)->create([
            'auditions_id'=>$audition->id
        ]);
        $response = $this->json('POST', 'api/appointments/auditions?token=' . $this->token, [
            'slot' => $slot->id,
            'email' => $user->email,
            'auditions' => $audition->id,
            'rol'=>$rol->id
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'image',
            'name',
            'time'
        ]]);
    }
    public function test_get_slots_by_audition_type_not_walk(){
        $user = factory(User::class)->create();
        $userDet = factory(UserDetails::class)->create(['user_id' => $user->id]);
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $appoinment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class,10)->create(['appointment_id' => $appoinment->id]);
        $response = $this->json('GET','api/appointments/show/'.$audition->id.'/notwalk?token='.$this->token);
        $response->assertStatus(200);
    }
    public function test_get_slots_by_audition_type_walk(){
        $user = factory(User::class)->create();
        $userDet = factory(UserDetails::class)->create(['user_id' => $user->id]);
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $appoinment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class,10)->create(['appointment_id' => $appoinment->id]);
        $response = $this->json('GET','api/appointments/show/'.$audition->id.'/walk?token='.$this->token);
        $response->assertStatus(200);
    }

    public function test_set_appoinment_audition_list()
    {
        $user = factory(User::class)->create();
        $userDet = factory(UserDetails::class)->create(['user_id' => $user->id]);
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $appoinment = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinment->id]);
        $rol = factory(Roles::class)->create([
            'auditions_id'=>$audition->id
        ]);
        factory(UserSlots::class,5)->create([
            'user_id'=>$user->id,
            'slots_id'=>$slot->id,
            'auditions_id'=>$audition->id,
            'roles_id'=>$rol->id,
            'status'=>'checked'
        ]);

        $response = $this->json('GET', 'api/appointments/auditions/'.$audition->id.'?token=' . $this->token);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            "image",
            "name",
            "time"
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

        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);
        $audition->media()->create(['url' => $this->faker->url, 'type' => 4,'name'=>'test']);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id
        ]);
        $this->rolId = $rol->id;
        $this->userId = $user->id;
        $this->auditionId = $audition->id;
    }
}
