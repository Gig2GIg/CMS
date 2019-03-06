<?php

namespace Tests\Unit;

use App\Http\Exceptions\User\DeleteUserException;
use App\Http\Exceptions\User\UpdateUserException;
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
    public function test_create_user()
    {
        $data = [
            'email' => $this->faker->email(),
            'password' => bcrypt($this->faker->word()),
            'first_name' => $this->faker->word(),
            'last_name' => $this->faker->word(),
            'type' => $this->faker->numberBetween($min = 1, $max = 3),
        ];

        $userRepo = new UserRepository(new User());
        $user = $userRepo->createUser($data);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['password'], $user->password);
        $this->assertEquals($data['type'], $user->type);
    }

    public function test_show_user()
    {
        $user = factory(User::class)->create();
        $userRepo = new UserRepository(new User());

        $found =  $userRepo->findUser($user->id);

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($found->first_name,$user->first_name);
        $this->assertEquals($found->last_name,$user->last_name);
        $this->assertEquals($found->email,$user->email);
        $this->assertEquals($found->password,$user->password);
        $this->assertEquals($found->type,$user->type);
    }

    public function test_update_user()
    {
        $user =factory(User::class)->create();
        $data = [
            'email' => $this->faker->email(),
            'password' => bcrypt($this->faker->word()),
            'first_name' => $this->faker->word(),
            'last_name' => $this->faker->word(),
            'type' => $this->faker->numberBetween($min = 1, $max = 3),
        ];

        $userRepo = new UserRepository($user);
        $update = $userRepo->updateUser($data);

        $this->assertTrue($update);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['password'], $user->password);
        $this->assertEquals($data['type'], $user->type);

    }

    public function test_delete_user()
    {
        $user = factory(User::class)->create();
        $userRepo = new UserRepository($user);

        $delete = $userRepo->deleteUser();

        $this->assertTrue($delete);
    }

    public function test_create_user_exception()
    {
        $this->expectException(UserCreateException::class);
        $userRepo = new UserRepository(new User());
        $userRepo->createUser([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(UserNotFoundException::class);
        $userRepo = new UserRepository(new User());
        $userRepo->findUser(28374);
    }

    public function test_update_user_exception()
    {
        $this->expectException(UpdateUserException::class);
        $user = factory(User::class)->create();
        $userRepo = new UserRepository($user);

        $data = ['email'=>null];

        $userRepo->updateUser($data);
    }

    public function test_user_delete_null()
    {
        $userRepo = new UserRepository(new User());
        $delete = $userRepo->deleteUser();

        $this->assertNull($delete);

    }
}
