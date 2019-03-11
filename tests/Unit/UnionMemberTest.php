<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-11
 * Time: 14:54
 */

namespace App\Http\Repositories;


use App\Http\Exceptions\UserUnionMembers\UserUnionCreateException;
use App\Http\Exceptions\UserUnionMembers\UserUnionNotFoundException;
use App\Http\Exceptions\UserUnionMembers\UserUnionUpdateException;
use App\Models\UserUnionMember;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnionMemberTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_all_unions_reg(){
        $data = factory(UserUnionMember::class)->create();
        $dataall = new UserUnionMemberRepository(new UserUnionMember());
        $dataall->create($data->toArray());
        $dataTest = $dataall->all();
        $this->assertIsArray($dataTest->toArray());
    }

    public function test_create_union_reg()
    {
        $data = factory(UserUnionMember::class)->create(); 

        $unionRepo = new UserUnionMemberRepository(new UserUnionMember());
        $userU = $unionRepo->create($data->toArray());
        $this->assertInstanceOf(UserUnionMember::class, $userU);
        $this->assertEquals($data['name'], $userU->name);
        $this->assertEquals($data['user_id'], $userU->user_id);
    }

    public function test_show_user_union()
    {
        $userU = factory(UserUnionMember::class)->create();
        $unionRepo = new UserUnionMemberRepository(new UserUnionMember());
        $found =  $unionRepo->find($userU->id);
        $this->assertInstanceOf(UserUnionMember::class, $found);
        $this->assertEquals($found->name,$userU->name);
        $this->assertEquals($found->user_id,$userU->user_id);
    }

    public function test_update_user_union()
    {
        $userU =factory(UserUnionMember::class)->create();
        $data = [
            'name' => $this->faker->company(),
            'user_id' => $this->faker->numberBetween(1,3),
        ];

        $unionRepo = new UserUnionMemberRepository($userU);
        $update = $unionRepo->update($data);

        $this->assertTrue($update);
        $this->assertEquals($data['name'], $userU->name);
        $this->assertEquals($data['user_id'], $userU->user_id);
    }

    public function test_delete_user_union()
    {
        $userU = factory(UserUnionMember::class)->create();
        $unionRepo = new UserUnionMemberRepository($userU);
        $delete = $unionRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_create_user_union_exception()
    {
        $this->expectException(UserUnionCreateException::class);
        $unionRepo = new UserUnionMemberRepository(new UserUnionMember());
        $unionRepo->create([]);
    }

    public function test_show_user_union_exception()
    {
        $this->expectException(UserUnionNotFoundException::class);
        $unionRepo = new UserUnionMemberRepository(new UserUnionMember());
        $unionRepo->find(28374);
    }

    public function test_update_user_union_exception()
    {
        $this->expectException(UserUnionUpdateException::class);
        $userU = factory(UserUnionMember::class)->create();
        $unionRepo = new UserUnionMemberRepository($userU);
        $data = ['name'=>null];
        $unionRepo->update($data);
    }

    public function test_user_delete_union_null()
    {
        $unionRepo = new UserUnionMemberRepository(new UserUnionMember());
        $delete = $unionRepo->delete();
        $this->assertNull($delete);

    }
}
