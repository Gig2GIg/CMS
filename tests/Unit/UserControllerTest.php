<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserUnionMembers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\JWTAuth;

class UserControllerTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;
    protected $token;
    protected $testId;




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
            'zip'=>'12345',
            'image'=>'http://test.com/image.jpg',
            'union_member' => [['name'=>'test1'], ['name'=>'test2']]
        ]);

        $response->assertStatus(201);
    }

    public function test_get_all_user_api_200()
    {

        factory(User::class,10)->create();
        $response = $this->json('POST', 'api/users?token='.$this->token);
        $response->assertStatus(200)->assertJson([
            "data"=>[]
        ]);

    }

    public function test_get_all_user_api_401()
    {
        User::query()->delete();
        $response = $this->json('POST', 'api/users?token='.$this->token);
        $response->assertStatus(401)->assertJson([
            "status"=> "token_error"
        ]);

    }

    public function test_create_user_api_422()
    {

        $response = $this->json('POST', 'api/users/create?token='.$this->token, [
            'email' => 'test@test.com',
            'password' => '123456',

        ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(422);
    }

    public function test_user_get_show_200(){
       $user =  factory(User::class)->create();
        $response = $this->json('POST', 'api/users/show/'.$user->id."?token=".$this->token);
        $response->assertStatus(200)->assertJsonStructure(['data'=> ['email']]);
    }

    public function test_user_get_show_404(){
        $response = $this->json('POST', 'api/users/show/99999'."?token=".$this->token);
        $response->assertStatus(404)->assertJson([
            "data"=>"Not found Data"
        ]);
    }

    public function test_edit_user_api_200()
    {

        $response = $this->json('PUT', 'api/users/update/'.$this->testId."?token=".$this->token, [
            'password' => '123456o',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'First Street #123',
            'city' => 'New York',
            'state' => '1',
            'birth' => '1980-05-24',
            'location' => '12,33334 - 23,00000',
            'zip'=>'00000',
            'stage_name'=>'test',
            'profesion'=>'test',
            'image'=>$this->faker->url
        ]);

        $response->assertStatus(200);
    }

    public function test_edit_user_api_404()
    {
        $response = $this->json('PUT', 'api/users/update/9999?token='.$this->token, [
            'password' => '123456',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'First Street #123',
            'city' => 'New York',
            'state' => '1',
            'birth' => '1980-05-24',
            'location' => '12,33334 - 23,00000',
            'zip'=>'00000',
            'stage_name'=>'test',
            'profesion'=>'test'
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_user_api_200(){
        $user = factory(User::class)->create();
        $userDetails = factory(UserDetails::class)->create([
            'user_id'=>$user->id,
        ]);
        $userMebership = factory(UserUnionMembers::class,2)->create([
            'user_id'=>$user->id,
        ]);
        $user->image()->create(['url' => $this->faker->url]);
        $response = $this->json('DELETE','api/users/delete/'.$user->id."?token=".$this->token);
        $response->assertStatus(200);
    }
    public function test_delete_user_api_400(){
        $user = factory(User::class)->create();
        $userDetails = factory(UserDetails::class)->create([
            'user_id'=>$user->id,
        ]);
        $userMebership = factory(UserUnionMembers::class,2)->create([
            'user_id'=>$user->id,
        ]);
        $user->image()->create(['url' => $this->faker->url]);
        $response = $this->json('DELETE','api/users/delete/9999?token='.$this->token);
        $response->assertStatus(404);
    }

    public function test_delete_user_api(){
        $user = factory(User::class)->create();

        $response = $this->json('DELETE','api/users/delete/'.$user->id."?token=".$this->token);
        $response->assertStatus(200);
    }

    public function test_send_email_200(){
        $user = factory(User::class)->create([
            'email'=>'alphyon21@gmail.com'
        ]);

        $response = $this->json('POST','api/remember',[
            'email'=>$user->email
        ])->assertStatus(200);
    }

    public function test_send_email_404(){
        $response = $this->json('POST','api/remember',[
            'email'=>"asdashsgdhs@abc.com"
        ])->assertStatus(404);
    }





}
