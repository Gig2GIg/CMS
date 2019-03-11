<?php

namespace Tests\Unit;

use App\Http\Exceptions\User\UserDeleteException;
use App\Http\Exceptions\UserDetails\UserDetailsCreateException;
use App\Http\Exceptions\UserDetails\UserDetailsNotFoundException;
use App\Http\Exceptions\UserDetails\UserDetailsUpdateException;
use App\Http\Repositories\UserDetailsRepository;
use App\Models\User;
use App\Models\UserDetails;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetailsUserUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_details_get_collection(){
        $data = factory(UserDetails::class,10)->create();
        $this->assertIsArray($data->toArray());
    }
    public function test_create_details()
    {
        $data = factory(UserDetails::class)->create()->toArray();
        $userDetailsRepo = new UserDetailsRepository(new UserDetails());
        $details = $userDetailsRepo->create($data);
        $this->assertInstanceOf(UserDetails::class, $details);
        $this->assertEquals($data['first_name'], $details->first_name);
        $this->assertEquals($data['city'], $details->city);
    }

    public function test_show_user()
    {
        $details = factory(UserDetails::class)->create();
        $userDetailsRepo = new UserDetailsRepository(new UserDetails());
        $found =  $userDetailsRepo->find($details->id);
        $this->assertInstanceOf(UserDetails::class, $found);
        $this->assertEquals($found->first_name,$details->first_name);
        $this->assertEquals($found->city,$details->city);
    }

    public function test_update_user()
    {
        $detail =factory(UserDetails::class)->create();
        $data = [
            'city' => $this->faker->city(),
            'first_name' => bcrypt($this->faker->firstName()),
        ];

        $userDetailRepo = new UserDetailsRepository($detail);
        $update = $userDetailRepo->update($data);

        $this->assertTrue($update);
        $this->assertEquals($data['city'], $detail->city);
        $this->assertEquals($data['first_name'], $detail->first_name);
    }

    public function test_delete_user()
    {
        $detail = factory(UserDetails::class)->create();
        $userDetailRepo = new UserDetailsRepository($detail);
        $delete = $userDetailRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_create_user_exception()
    {
        $this->expectException(UserDetailsCreateException::class);
        $userDetailRepo = new UserDetailsRepository(new UserDetails());
        $userDetailRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(UserDetailsNotFoundException::class);
        $userDetailRepo = new UserDetailsRepository(new UserDetails());
        $userDetailRepo->find(28374);
    }

    public function test_update_user_exception()
    {
        $this->expectException(UserDetailsUpdateException::class);
        $userDet = factory(UserDetails::class)->create();
        $userDetailRepo = new UserDetailsRepository($userDet);
        $data = ['city'=>null];
        $userDetailRepo->update($data);
    }

    public function test_user_delete_null()
    {
        $userDetailRepo = new UserDetailsRepository(new UserDetails());
        $delete = $userDetailRepo->delete();
        $this->assertNull($delete);

    }
}
