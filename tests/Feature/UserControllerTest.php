<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Notifications\NotificationSetting;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserUnionMembers;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\JWTAuth;

class UserControllerTest extends TestCase
{


    protected $token;
    protected $testId;



    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create([
                'email' => $this->faker->email,
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;
        $user->image()->create(['url' => $this->faker->url,'name'=>$this->faker->word()]);
        $userDetails = factory(UserDetails::class)->create([
            'type'=>2,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token');


    }




    public function test_create_user_api_201()
    {


        $response = $this->json('POST', 'api/users/create', [
            'email' => $this->faker->email,
            'password' => '123456',
            'type' => '2',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'First Street #123',
            'city' => 'New York',
            'state' => '1',
            'birth' => '1980-05-24',
//            'location' => '12,33334 - 23,00000',
            'stage_name'=>'test',
            'profesion'=>'lawyer',
            'zip'=>'12345',
            'image'=>'http://test.com/image.jpg',
            'url'=>$this->faker->url,
            'resource_name'=>'test',
            'union_member' => [['name'=>'test1'], ['name'=>'test2']]
        ]);

        $response->assertStatus(201);
    }

    public function test_get_all_user_api_200()
    {

        factory(User::class,10)->create();
        $response = $this->json('GET', 'api/a/users?token='.$this->token);
        $response->assertStatus(200)->assertJson([
            "data"=>[]
        ]);

    }

    public function test_get_all_user_api_401()
    {
        User::query()->delete();
        $response = $this->json('GET', 'api/a/users?token='.$this->token);
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
        $response = $this->json('GET', 'api/a/users/show/'.$user->id."?token=".$this->token);
        $response->assertStatus(200)->assertJsonStructure(['data'=> ['email']]);
    }

    public function test_user_get_show_404(){
        $response = $this->json('GET', 'api/a/users/show/99999'."?token=".$this->token);
        $response->assertStatus(404)->assertJson([
            "data"=>"Not found Data"
        ]);
    }

    public function test_edit_user_api_200()
    {

        $response = $this->json('PUT', 'api/a/users/update/'.$this->testId."?token=".$this->token, [
            'email'=>'test12345@test.com',
            'password' => '123456o',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'First Street #123',
            'city' => 'New York',
            'state' => '1',
            'birth' => '1980-05-24',
            'url'=>$this->faker->url,
//            'location' => '12,33334 - 23,00000',
            'zip'=>'00000',
            'stage_name'=>'test',
            'profesion'=>'test',
            'image'=>$this->faker->url
        ]);

        $response->assertStatus(200);
    }

    public function test_edit_user_api_404()
    {
        $response = $this->json('PUT', 'api/a/users/update/9999?token='.$this->token, [
            'password' => '123456',
            'email'=>'test1234567@test.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'First Street #123',
            'city' => 'New York',
            'state' => '1',
            'birth' => '1980-05-24',
//            'location' => '12,33334 - 23,00000',
            'zip'=>'00000',
            'stage_name'=>'test',
            'profesion'=>'test'
        ]);

        $response->assertStatus(404);
    }

    public function test_update_user_membership_api_200(){

        $userMebership = factory(UserUnionMembers::class,2)->create([
            'user_id'=>$this->testId,
        ]);
        $data = [];

        foreach ($userMebership as $item){
            $data[]= [

              'name'=>$this->faker->word(),

            ];
        }

        $response = $this->json('PUT','api/a/users/union/update?token='.$this->token,[
            'data'=> $data
        ]);
        $response->assertStatus(200);
    }

    public function test_list_user_membership_api_200(){

        $userMebership = factory(UserUnionMembers::class,2)->create([
            'user_id'=>$this->testId,
        ]);

        $response = $this->json('GET','api/a/users/union/list?token='.$this->token);
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

    public function test_send_email_admin_200(){
        $user = factory(Admin::class)->create([
            'email'=>'alphyon21@gmail.com'
        ]);

        $response = $this->json('POST','api/remember/admin',[
            'email'=>$user->email
        ]);
        $response->assertStatus(200);
    }

    public function test_send_email_404(){
        $response = $this->json('POST','api/remember',[
            'email'=>"asdashsgdhs@abc.com"
        ])->assertStatus(404);
    }





}
