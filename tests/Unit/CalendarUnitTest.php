<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\Calendar;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Repositories\CalendarRepository;

class CalendarUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;


    protected $user_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $this->user_id = $user->id;
    }

    public function test_all_events(){
        factory(Calendar::class,5)->create();
        $dataAll = new CalendarRepository(new Calendar());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function test_create_event()
    {
        $dt = Carbon::now();

        $data = [
            'production_type' => $this->faker->name,
            'project_name' => $this->faker->name,
            'start_date' => $dt->toDateString(),
            'end_date' => $dt->toDateString(),
            'user_id' => $this->user_id,
        ];

        $calendarRepo = new CalendarRepository(new Calendar());
        $calendar = $calendarRepo->create($data);
        $this->assertInstanceOf(Calendar::class, $calendar);
        $this->assertEquals($data['production_type'], $calendar->production_type);
        $this->assertEquals($data['project_name'], $calendar->project_name);
        $this->assertEquals($data['start_date'], $calendar->start_date);
        $this->assertEquals($data['end_date'], $calendar->end_date);
        $this->assertEquals($data['user_id'], $calendar->user_id);

    }

    public function test_show_event()
    {
        $calendar = factory(Calendar::class)->create();
        $calendarRepo = new CalendarRepository(new Calendar());
        $found =  $calendarRepo->find($calendar->id);
        $this->assertInstanceOf(Calendar::class, $found);
        $this->assertEquals($found->production_type,$calendar->production_type);
        $this->assertEquals($found->project_name,$calendar->project_name);
        $this->assertEquals($found->start_date,$calendar->start_date);
        $this->assertEquals($found->end_date,$calendar->end_date);
    }

    public function test_create_event_exception()
    {
        $this->expectException(CreateException::class);
        $calendarRepo = new CalendarRepository(new Calendar());
        $calendarRepo->create([]);
    }

    public function test_show_event_exception()
    {
        $this->expectException(NotFoundException::class);
        $calendarRepo = new CalendarRepository(new Calendar());
        $calendarRepo->find(2345);
    }

    public function test_update_event_exception()
    {
        $this->expectException(UpdateException::class);
        $calendar = factory(Calendar::class)->create();
        $calendarRepo = new CalendarRepository($calendar);
        $data = ['project_name'=>null];
        $calendarRepo->update($data);
    }

    public function test_event_delete_null()
    {
        $calendarRepo = new CalendarRepository(new Calendar());
        $delete = $calendarRepo->delete();
        $this->assertNull($delete);

    }

}
