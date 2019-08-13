<?php

namespace Tests\Unit;

use App\Http\Exceptions\NotFoundException;
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
            'performer_id' => $performer->id,
            'director_id' => $director->id,
            'uuid' => $this->faker->uuid,
        ]);

        $this->assertInstanceOf(Performers::class, $data);
    }

    public function test_find_uuid()
    {
        $director = factory(User::class)->create();
        $performer = factory(User::class)->create();
        $db_performer = factory(Performers::class)->create([
                'performer_id' => $performer->id,
                'director_id' => $director->id,
                'uuid' => $this->faker->uuid,
            ]
        );

        $repo = new PerformerRepository(new Performers());
        $data = $repo->findbyparam('uuid', $db_performer->uuid)->first();
        $this->assertInstanceOf(Performers::class, $data);
        $this->assertEquals($data->performer_id, $db_performer->performer_id);
    }

    public function test_find_director_register()
    {
        $director = factory(User::class)->create();
        $performer = factory(User::class)->create();
        $db_performer = factory(Performers::class)->create([
                'performer_id' => $performer->id,
                'director_id' => $director->id,
                'uuid' => $this->faker->uuid,
            ]
        );
        $director2 = factory(User::class)->create();

        $repo = new PerformerRepository(new Performers());
        $data = $repo->findbyparam('uuid', $db_performer->uuid)->first();
        $this->assertInstanceOf(Performers::class, $data);
        $this->assertNotSame($data->director_id, $director2->id);
    }

    public function test_list_performers_by_director()
    {
        $data = factory(User::class, 10)->create();
        $director = factory(User::class)->create();
        $data->each(function ($item) use ($director) {
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $director->id,
                'uuid' => $this->faker->uuid,
            ]);
        });
        $repo = new PerformerRepository(new Performers());

        $perfomer = $repo->findbyparam('director_id', $director->id)->get();
        $count = $perfomer->count();

        $this->assertTrue($count > 1);

    }

    public function test_not_found()
    {
        $this->expectException(NotFoundException::class);
        $repo = new PerformerRepository(new Performers());

        $perfomer = $repo->find(999);


    }


}
