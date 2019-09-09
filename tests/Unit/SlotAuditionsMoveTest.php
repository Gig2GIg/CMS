<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Resources;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserManager;
use App\Models\UserSlots;
use Tests\TestCase;

class SlotAuditionsMoveTest extends TestCase
{
    protected $userId;
    protected $userId2;
    protected $auditionId;
    protected $rolId;
    protected $token;
    protected $slots;
    protected $appoimentId;
    protected $user_slot;
    protected $user_slots;
    protected $users;

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
        
        // CREATED USER TYPE APP
        $user2 = factory(User::class)->create([
                'email' => 'app@test.com',
                'password' => bcrypt('123456')]
        );
        $user2->image()->create(['url' => $this->faker->url,'name'=>$this->faker->word()]);

        $userDetails2 = factory(UserDetails::class)->create([
            'type' => 2,
            'user_id' => $user2->id,
        ]);

        // =========================

        // CREATED AUDITIONS WITH USER TYPE TABLE
        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);

        $audition->media()->create(['url' => $this->faker->url, 'type' => 4, 'name' => 'test']);
        
        // CREATED ROTES TO AUIDITIONS WITH USER TYPE TABLE
        $rols = factory(Roles::class, 12)->create([
            'auditions_id' => $audition->id
        ]);

        // CREATED APPOIMENT TO AUIDITIONS WITH USER TYPE TABLE
        $appoiment = factory(Appointments::class)->create([
            'auditions_id' => $audition->id
        ]);
         // CREATED SLOTS WITH APPOIMENTS ID WITH USER TYPE TABLE
        $slots = factory(Slots::class, 12)->create([
            'appointment_id' => $appoiment->id
        ]);

        // CREATED REQUEST UPCOMMING WIT WITH USER TYPE APP
        $user_audition = factory(UserAuditions::class)->create([
            'user_id' => $user2->id,
            'auditions_id' => $audition->id,
            'rol_id' =>  $rols[0]->id,
            'slot_id' => $slots[0]->id,
            'type' => 1
        ]);

        // CREATED USERSLOTS
        $user_slot = factory(UserSlots::class)->create([
            'user_id' => $user2->id,
            'auditions_id' => $audition->id,
            'slots_id' =>  $slots[0]->id,
            'roles_id' => $rols[0]->id,
            'status' => 'reserved', //'checked'
            'favorite' => 1
        ]);

        $users= factory(User::class, 12)->create();
    
        // CREATE USER DETAILS FROM USERS PULL
        foreach ($users as $user) {
            $userDetails = factory(UserDetails::class)->create([
                'type' => 2,
                'user_id' => $user->id,
            ]);
        }

          // CREATED REQUEST UPCOMMING WIT WITH USER TYPE APP
          $user_audition = factory(UserAuditions::class)->create([
            'user_id' => $users[1]->id,
            'auditions_id' => $audition->id,
            'rol_id' =>  $rols[1]->id,
            'slot_id' => $slots[1]->id,
            'type' => 1
        ]);

        // CREATED USERSLOTS1
        $user_slot = factory(UserSlots::class)->create([
            'user_id' => $users[1]->id,
            'auditions_id' => $audition->id,
            'slots_id' =>  $slots[1]->id,
            'roles_id' => $rols[1]->id,
            'status' => 'reserved', //'checked'
            'favorite' => 1
        ]);

         // CREATED USERSLOTS 2
         $user_slot2 = factory(UserSlots::class)->create([
            'user_id' => $users[2]->id,
            'auditions_id' => $audition->id,
            'slots_id' =>  $slots[2]->id,
            'roles_id' => $rols[2]->id,
            'status' => 'reserved', //'checked'
            'favorite' => 1
        ]);

          // CREATED USERSLOTS 3
          $user_slot3 = factory(UserSlots::class)->create([
            'user_id' => $users[3]->id,
            'auditions_id' => $audition->id,
            'slots_id' =>  $slots[6]->id,
            'roles_id' => $rols[4]->id,
            'status' => 'reserved', //'checked'
            'favorite' => 1
        ]);

        // CREATED USERSLOTS 4
        $user_slot3 = factory(UserSlots::class)->create([
            'user_id' => $users[4]->id,
            'auditions_id' => $audition->id,
            'slots_id' =>  $slots[7]->id,
            'roles_id' => $rols[8]->id,
            'status' => 'reserved', //'checked'
            'favorite' => 1
        ]);



        $this->rolId = $rols->first()->id;
        $this->userId2 = $user2->id;
        $this->auditionId = $audition->id;
        $this->slots = $slots;
        $this->appoimentId = $appoiment->id;
        $this->user_slot = $user_slot->id;
        $this->users = $users;
        $this->user_slots = UserSlots::all();
    }



    public function test_it_reorder_appointment_times_slots200()
    {
        $data = [
                'slots' => [
                    [
                        'slot_id' =>  $this->slots[4]->id,
                        'user_id' =>  $this->userId2
                    ],
                    [
                    
                        'slot_id' =>  $this->slots[2]->id,
                        'user_id' =>  $this->users[1]->id
                    ],
                    [
                        'slot_id' =>  $this->slots[5]->id,
                        'user_id' =>  $this->users[3]->id
                    ],
                    [
                        'slot_id' =>  $this->slots[6]->id,
                        'user_id' =>  $this->users[4]->id
                    ]
                ]];
            
    
        $response = $this->json('PUT',
            'api/t/auditions/appointments/'. $this->appoimentId.'/slots?'. '&token=' . $this->token,
            $data
        );



        $response->assertStatus(200);
       
    }
}


