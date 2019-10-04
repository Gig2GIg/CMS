<?php

namespace Tests\Feature;

use App\Models\Auditions;
use App\Models\Appointments;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Admin;
use App\Models\Roles;
use App\Models\Slots;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionsTest extends TestCase
{
    protected $user;
    protected $token;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(Admin::class)->create([
                'email' => 'cms@test.com',
                'name' => 'Test',
                'password' => bcrypt('123456')]
        );
        $response = $this->post('api/admin/login', [
            'email' => 'cms@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('token'); 
    }

    public function test_list_auditions_pending_200()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id, 'banned'=> 'pending']);
        $roles = factory(Roles::class)->create(['auditions_id' => $audition->id]);
        $appoinments = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinments->id]);
        
        $response = $this->json('GET',
            'api/cms/auditions-pending'. '?token='. $this->token);
        $response->assertStatus(200);
    }
}