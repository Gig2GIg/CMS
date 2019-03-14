<?php

namespace Tests\Unit;

use App\Http\Repositories\AuditionRepository;
use App\Models\Auditions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionsUnitTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_create_auditions()
    {

        $data = factory(Auditions::class)->create();

        $auditionsRepo = new AuditionRepository(new Auditions());
        $audition = $auditionsRepo->create($data->toArray());
        $this->assertInstanceOf(Auditions::class, $audition);
        $this->assertEquals($data['title'], $audition->name);
        $this->assertEquals($data['date'], $audition->user_id);
    }
}
