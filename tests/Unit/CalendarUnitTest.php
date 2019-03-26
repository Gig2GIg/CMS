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

    /**
     * @test
     */

    protected $user_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $this->user_id = $user->id;
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

}
