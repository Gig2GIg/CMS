<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\RolesRepository;
use App\Http\Resources\RolesResource;
use Illuminate\Http\Request;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;

class RolesControllerTest extends TestCase
{
    protected $token;
    protected $audition_id;
    protected $user_id;

    public function setUp(): void
    {
        parent::setUp();

        $director = factory(User::class)->create();
        $this->user_id = $director->id;

        $audition = factory(Auditions::class)->create(['user_id'=>$director->id]);
        $this->audition_id = $audition->id;

        $user = factory(User::class)->create([
                'email' => 'token@test.com',
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;
        $user->image()->create(['url' => $this->faker->url,'name'=>$this->faker->word()]);
        $userDetails = factory(UserDetails::class)->create([
            'type'=>1,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token');
    }

    public function test_get_roles_200()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        factory(Roles::class, 2)->create(['auditions_id' => $audition->id]);
        
        $response = $this->json('GET', 'api/t/roles?token=' . $this->token);

        $response->assertStatus(200);
        $dataj = json_decode($response->content(), true);
        $count = count($dataj['data']);
        $this->assertTrue($count > 0);
    
        $response->assertJsonStructure(['data' => [[
            "id",
            "name",
            "description",
            "auditions_id",
        ]]]);
    }

    public function test_create_role_201()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $roles = factory(Roles::class)->create(['auditions_id' => $audition->id]);

        $data = [
            'name' => 'Hero',
            'description' => 'Main character',
            'auditions_id' => $this->audition_id
        ];

        $response = $this->json('POST', 'api/t/roles/create?token=' . $this->token, $data);
        $response->assertStatus(201);
    }

    public function test_delete_role_200()
    {
        $role = factory(Roles::class)->create(['auditions_id' => $this->audition_id]);
        $response = $this->json('DELETE', 'api/t/roles/'.$role->id .'/delete' .'?token=' . $this->token);

        $response->assertStatus(200);
    }
}
