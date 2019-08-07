<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\RecommendationsRepository;
use App\Models\Recommendations;
use Tests\TestCase;

class RecommendationsUnitTest extends TestCase
{
    public function test_create_recommendations()
    {
        $repo = new RecommendationsRepository(New Recommendations());
        $data = [
            'marketplace_id' => $this->faker->numberBetween(1,2),
            'user_id' => $this->faker->numberBetween(1,2),
            'audition_id' => $this->faker->numberBetween(1,2),
        ];
        $recommendations = $repo->create($data);
        $this->assertInstanceOf(Recommendations::class, $recommendations);
        $this->assertEquals($data->user_id, $recommendations->name);
        $this->assertEquals($data->auditions_id, $recommendations->rol);

    }
    public function test_create_recommendations_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new RecommendationsRepository(New Recommendations());
        $recommendations = $repo->create([]);
        $this->assertInstanceOf(Recommendations::class, $recommendations);
    }
    public function test_recommendations_get_all(){
        factory(Recommendations::class, 5)->create();
        $recommendations = new RecommendationsRepository(new Recommendations());
        $data = $recommendations->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }
    public function test_show_recommendations()
    {
        $recommendations = factory(Recommendations::class)->create();
        $repo = new RecommendationsRepository(new Recommendations());
        $found =  $repo->find($recommendations->id);
        $this->assertInstanceOf(Recommendations::class, $found);
        $this->assertEquals($found->name,$recommendations->name);
        $this->assertEquals($found->rol,$recommendations->rol);
    }

    public function test_update_recommendations()
    {
        $recommendations =factory(Recommendations::class)->create();
        $data = [
            'name' => $this->faker->word(),
        ];

        $repo = new RecommendationsRepository($recommendations);
        $update = $repo->update($data);

        $this->assertTrue($update);

    }

    public function test_delete_recommendations()
    {
        $recommendations = factory(Recommendations::class)->create();
        $repo = new RecommendationsRepository($recommendations);
        $delete = $repo->delete();
        $this->assertTrue($delete);
    }

    public function test_show_recommendations_exception()
    {
        $this->expectException(NotFoundException::class);
        $repo = new RecommendationsRepository(new Recommendations());
        $repo->find(28374);
    }
    public function test_update_recommendations_exception()
    {
        $this->expectException(UpdateException::class);
        $recommendations = factory(Recommendations::class)->create();
        $repo = new RecommendationsRepository($recommendations);
        $data = ['name'=>null];
        $repo->update($data);
    }

    public function test_recommendations_delete_null()
    {
        $repo = new RecommendationsRepository(new Recommendations());
        $delete = $repo->delete();
        $this->assertNull($delete);

    }


}
