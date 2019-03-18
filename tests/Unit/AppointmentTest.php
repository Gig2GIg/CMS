<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AppointmentRepository;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    protected $audition_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id'=>$user->id]);
        $this->audition_id= $audition->id;
    }

    public function test_create_appointment()
    {
        $data = factory(Appointments::class)->create(['audition_id' => $this->audition_id]);
        $appointmentRepo = new AppointmentRepository(new Appointments());
        $appointment = $appointmentRepo->create($data->toArray());
        $this->assertInstanceOf(Appointments::class, $appointment);
        $this->assertEquals($data['slots'], $appointment->slots);
        $this->assertEquals($data['length'], $appointment->length);
    }

   

  
    public function test_find_appointment()
    {
        $data = factory(Appointments::class)->create(['audition_id' => $this->audition_id]);
        $appointmentRepo = new AppointmentRepository(new Appointments());
        $found = $appointmentRepo->find($data->id);
        $this->assertInstanceOf(Appointments::class, $found);
        $this->assertEquals($found->slots, $data->slots);
        $this->assertEquals($found->length, $data->length);
    }
    public function test_delete_appointment()
    {
        $data = factory(Appointments::class)->create(['audition_id' => $this->audition_id]);
        $appointmentRepo = new AppointmentRepository($data);
        $delete = $appointmentRepo->delete();
        $this->assertTrue($delete);
    }
    public function test_all_appointment()
    {
        factory(Appointments::class, 5)->create(['audition_id' => $this->audition_id]);
        $appointment = new AppointmentRepository(new Appointments());
        $data = $appointment->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_appointment_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new AppointmentRepository(new Appointments());
        $userRepo->create([]);
    }

    public function test_show_appointment_exception()
    {
        $this->expectException(NotFoundException::class);
        $appointment = new AppointmentRepository(new Appointments());
        $appointment->find(2345);
    }

   

    public function test_delete_appointment_null_exception()
    {

        $appointmentRepo = new AppointmentRepository(new Appointments());
        $delete = $appointmentRepo->delete();
        $this->assertNull($delete);
    }

}
