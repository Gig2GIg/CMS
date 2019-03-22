<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\SlotsRepository;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Slots;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SlotTest extends TestCase
{

    protected $appointment_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id'=>$user->id]);
        $appointment = factory(Appointments::class)->create(['auditions_id'=>$audition->id]);
        $this->appointment_id= $appointment->id;
    }

    public function test_create_slots()
    {
        $data = factory(Slots::class)->create(['appointment_id' => $this->appointment_id]);
        $slotsRepo = new SlotsRepository(new slots());
        $slots = $slotsRepo->create($data->toArray());
        $this->assertInstanceOf(Slots::class, $slots);
        $this->assertEquals($data['time'], $slots->time);
        $this->assertEquals($data['status'], $slots->status);
    }




    public function test_find_slots()
    {
        $data = factory(Slots::class)->create(['appointment_id' => $this->appointment_id]);
        $slotsRepo = new SlotsRepository(new slots());
        $found = $slotsRepo->find($data->id);
        $this->assertInstanceOf(Slots::class, $found);
        $this->assertEquals($found->time, $data->time);
        $this->assertEquals($found->status, $data->status);
    }

    public function test_all_slots()
    {
        factory(Slots::class, 5)->create(['appointment_id' => $this->appointment_id]);
        $slots = new SlotsRepository(new slots());
        $data = $slots->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_slots_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new SlotsRepository(new slots());
        $userRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $slots = new SlotsRepository(new slots());
        $slots->find(2345);
    }


}
