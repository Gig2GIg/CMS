<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\RolesRepository;
use App\Models\Roles;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolesUnitTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_create_roles()
    {
        $data = factory(Roles::class)->create();
        $rolesRepo = new RolesRepository(new Roles());
        $roles = $rolesRepo->create($data->toArray());
        $this->assertInstanceOf(Roles::class, $roles);
        $this->assertEquals($data['name'], $roles->name);
        $this->assertEquals($data['description'], $roles->description);
    }

    public function test_edit_roles()
    {
        $data = factory(Roles::class)->create();
        $dataUpdate = [
            'title' => $this->faker->title(),
            'description' => $this->faker->paragraph(),
        ];
        $rolesRepo = new RolesRepository($data);
        $roles = $rolesRepo->update($dataUpdate);
        $this->assertTrue($roles);


    }

    public function test_delete_roles()
    {
        $data = factory(Roles::class)->create();
        $rolesRepo = new RolesRepository($data);
        $delete = $rolesRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_roles()
    {
        $data = factory(Roles::class)->create();
        $rolesRepo = new RolesRepository(new roles());
        $found = $rolesRepo->find($data->id);
        $this->assertInstanceOf(Roles::class,$found);
        $this->assertEquals($found->title,$data->title);
        $this->assertEquals($found->description,$data->description);
    }

    public function test_all_roles()
    {
        factory(Roles::class,5)->create();
        $roles = new RolesRepository(new roles());
        $data = $roles->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_roles_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new rolesRepository(new roles());
        $userRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $roles = new RolesRepository(new roles());
        $roles->find(2345);
    }

    public function test_update_roles_exception()
    {
        $this->expectException(UpdateException::class);
        $roles = factory(Roles::class)->create();
        $rolesRepo = new RolesRepository($roles);
        $data = ['name'=>null];
        $rolesRepo->update($data);
    }

    public function test_delete_roles_null_exception()
    {

        $rolesRepo = new RolesRepository(new roles());
        $delete = $rolesRepo->delete();
        $this->assertNull($delete);
    }

}
