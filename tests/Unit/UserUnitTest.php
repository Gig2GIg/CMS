<?php

namespace Tests\Unit;

use App\Http\Exceptions\User\UserUpdateException;
use App\Http\Exceptions\User\UserCreateException;
use App\Http\Exceptions\User\UserNotFoundException;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */

    public function test_all_users(){
        $data = [
            'email' => $this->faker->email(),
            'password' => bcrypt($this->faker->word()),
        ];
        $dataAll = new UserRepository(new User());
        $dataAll->create($data);
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
}

    public function test_create_user()
    {
        $data = [
            'email' => $this->faker->email(),
            'password' => bcrypt($this->faker->word()),
        ];

        $userRepo = new UserRepository(new User());
        $user = $userRepo->create($data);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['password'], $user->password);
    }

    public function test_show_user()
    {
        $user = factory(User::class)->create();
        $userRepo = new UserRepository(new User());
        $found =  $userRepo->find($user->id);
        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($found->email,$user->email);
        $this->assertEquals($found->password,$user->password);
    }

    public function test_update_user()
    {
        $user =factory(User::class)->create();
        $data = [
            'email' => $this->faker->email(),
            'password' => bcrypt($this->faker->word()),
        ];

        $userRepo = new UserRepository($user);
        $update = $userRepo->update($data);

        $this->assertTrue($update);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['password'], $user->password);
    }

    public function test_delete_user()
    {
        $user = factory(User::class)->create();
        $userRepo = new UserRepository($user);
        $delete = $userRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_create_user_exception()
    {
        $this->expectException(UserCreateException::class);
        $userRepo = new UserRepository(new User());
        $userRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(UserNotFoundException::class);
        $userRepo = new UserRepository(new User());
        $userRepo->find(28374);
    }

    public function test_update_user_exception()
    {
        $this->expectException(UserUpdateException::class);
        $user = factory(User::class)->create();
        $userRepo = new UserRepository($user);
        $data = ['email'=>null];
        $userRepo->update($data);
    }

    public function test_user_delete_null()
    {
        $userRepo = new UserRepository(new User());
        $delete = $userRepo->delete();
        $this->assertNull($delete);

    }
}
