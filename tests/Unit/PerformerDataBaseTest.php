<?php

namespace Tests\Unit;

use App\Http\Repositories\PerformerRepository;
use App\Models\Performers;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerformerDataBaseTest extends TestCase
{

    public function test_create_performer_director()
    {
        $director = factory(User::class)->create();
        $performer = factory(User::class)->create();
        $repo = new PerformerRepository(new Performers());
        $data = $repo->create([
            'performer_id'=>$performer->id,
            'director_id'=>$director->id,
            'uuid'=>$this->faker->uuid,
        ]);

        $this->assertInstanceOf(Performers::class,$data);
    }
}
