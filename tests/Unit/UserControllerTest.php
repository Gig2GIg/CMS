<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_create_user_api_201()
    {

        $response = $this->json('POST', 'api/users/create', [
            'email' => 'test@test.com',
            'password' => '123456',
            'type' => '2',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'First Street #123',
            'city' => 'New York',
            'state' => '1',
            'birth' => '1980-05-24',
            'location' => '12,33334 - 23,00000',
            'stage_name'=>'test',
            'profesion'=>'lawyer',
            'union_member' => [['name'=>'test1'], ['name'=>'test2']]
        ]);

        $response->assertStatus(201);
    }

    public function test_get_all_user_api_200()
    {
        factory(User::class,10)->create();
        $response = $this->json('POST', 'api/users');
        $response->assertStatus(200)->assertJson([
            "data"=>[]
        ]);

    }

    public function test_get_all_user_api_404()
    {

        $response = $this->json('POST', 'api/users');
        $response->assertStatus(404)->assertJson([
            "data"=>'Not found Data'
        ]);

    }

    public function test_create_user_api_422()
    {

        $response = $this->json('POST', 'api/users/create', [
            'email' => 'test@test.com',
            'password' => '123456',

        ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(422);
    }

    public function test_user_get_show_200(){
       $user =  factory(User::class)->create();
        $response = $this->json('POST', 'api/users/show/'.$user->id);
        $response->assertStatus(200)->assertJsonStructure(['data'=> ['email']]);
    }

    public function test_user_get_show_404(){
        $response = $this->json('POST', 'api/users/show/99999');
        $response->assertStatus(404)->assertJson([
            "data"=>"Not found Data"
        ]);
    }

    public function test_edit_user_api_200()
    {
    $data=1;
        $response = $this->json('POST', 'api/users/update/'.$data, [
            'email' => 'test@test.com',
            'password' => '123456',
            'type' => '2',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'First Street #123',
            'city' => 'New York',
            'state' => '1',
            'birth' => '1980-05-24',
            'location' => '12,33334 - 23,00000',
            'union_member' => [['name'=>'test1'], ['name'=>'test2']]
        ]);

        $response->assertStatus(200);
    }


}
