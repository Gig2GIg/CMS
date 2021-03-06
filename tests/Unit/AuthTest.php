<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = new UserRepository(new User());

        $user->create([
            'email' => 'test@test.com',
            'password' => bcrypt('123456'),
        ]);
    }

    public function test_token_login()
    {


        $response = $this->post('api/login', [
            'email' => 'test@test.com',
            'password' => '123456',
        ]);

        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);

    }

    public function test_token_not_authorizate()
    {
        $response = $this->post('api/login', [
            'email' => 'test1@test.com',
            'password' => '123456dsdsd',
        ]);

        $response->assertJsonStructure([
            'error',
        ]);
    }

    public function test_logout()
    {
        $this->post('api/login', [
            'email' => 'test@test.com',
            'password' => '123456',
        ]);

        $response = $this->post('api/logout');

        $response->assertJsonStructure([
            'message'
        ]);
    }
}
