<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\UserManagerRepository;
use App\Models\User;
use App\Models\UserManager;
use Tests\TestCase;


class UserManagerTest extends TestCase
{
    protected $userId;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create();
        $this->userId = $user->id;
    }

    public function test_create_user_manager()
    {
        $repo = new UserManagerRepository(New UserManager());
        $data = factory(UserManager::class)->create([
            'user_id'=>$this->userId,

        ]);
        $manager_user = $repo->create($data->toArray());
        $this->assertInstanceOf(UserManager::class, $manager_user);
        $this->assertEquals($data->name,$manager_user->name);
        $this->assertEquals($data->company,$manager_user->company);

    }
    public function test_create_user_manager_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new UserManagerRepository(New UserManager());
        $manager_user = $repo->create([]);
        $this->assertInstanceOf(UserManager::class, $manager_user);
    }

    public function test_show_manager_user()
    {
        $manager_user = factory(UserManager::class)->create([
            'user_id'=>$this->userId,

        ]);
        $manager_userRepo = new UserManagerRepository(new UserManager());
        $found =  $manager_userRepo->find($manager_user->id);
        $this->assertInstanceOf(UserManager::class, $found);
        $this->assertEquals($found->name,$manager_user->name);
        $this->assertEquals($found->rol,$manager_user->rol);
    }



    public function test_update_manager_user()
    {
        $data = [
            'name'=>$this->faker->name(),
            'company'=>$this->faker->company(),
            'email'=>$this->faker->safeEmail(),
            'type'=>$this->faker->numberBetween(1,2),
            'notifications'=>$this->faker->boolean(),
        ];
        $manager_user = factory(UserManager::class)->create([
            'user_id'=>$this->userId,

        ]);
        $manager_userRepo = new UserManagerRepository($manager_user);
        $update = $manager_userRepo->update($data);
        $this->assertTrue($update);
    }

    public function test_show_manager_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $manager_userRepo = new UserManagerRepository(new UserManager());
        $manager_userRepo->find(28374);
    }



}