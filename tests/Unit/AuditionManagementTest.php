<?php

namespace Tests\Unit;

use App\Models\Auditions;
use App\Models\Resources;
use App\Models\Roles;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserManager;
use Tests\TestCase;

class AuditionManagementTest extends TestCase
{
    protected $userId;
    protected $auditionId;
    protected $rolId;
    protected $token;

    public function test_save_upcoming_audition()
    {

        $response = $this->json('POST',
            'api/a/auditions?token=' . $this->token,
            [
                'auditions' => $this->auditionId,
                'rol' => $this->rolId,
                'type' => 1
            ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);
    }

    public function test_save_requested()
    {
        factory(UserManager::class)->create([
            'user_id' => $this->userId,
            'notifications' => true,
            'email' => 'alphyon21@gamial.com'
        ]);

        $response = $this->json('POST',
            'api/a/auditions?token=' . $this->token,
            [
                'auditions' => $this->auditionId,
                'rol' => $this->rolId,
                'type' => 2
            ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);
    }

    public function test_save_upcoming_audition_error()
    {

        $response = $this->json('POST',
            'api/a/auditions?token=' . $this->token,
            [
                'auditions' => 20,
                'rol' => 34,
                'type' => 1
            ]);
        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }

    public function test_auditions_upcomming()
    {

        factory(UserAuditions::class, 10)->create([
            'user_id' => $this->userId,
            'rol_id' => $this->rolId,
            'auditions_id' => $this->auditionId,
            'type' => 1,
        ]);
        $response = $this->json('GET',
            'api/a/auditions/upcoming?token=' . $this->token);
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

    public function test_auditions_request()
    {

        factory(UserAuditions::class, 10)->create([
            'user_id' => $this->userId,
            'rol_id' => $this->rolId,
            'auditions_id' => $this->auditionId,
            'type' => 2,
        ]);
        $response = $this->json('GET',
            'api/a/auditions/requested?token=' . $this->token);
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
