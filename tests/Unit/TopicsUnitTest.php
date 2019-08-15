<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\TopicsRepository;
use App\Models\Topics;
use Tests\TestCase;

class TopicsUnitTest extends TestCase
{
    public function test_create_topic()
    {
        $repo = new TopicsRepository(New Topics());
        $data = factory(Topics::class)->create();
        $topic = $repo->create($data->toArray());
        $this->assertInstanceOf(Topics::class, $topic);
        $this->assertEquals($data->name,$topic->name);
        $this->assertEquals($data->rol,$topic->rol);

    }
    public function test_create_topic_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new TopicsRepository(New Topics());
        $topic = $repo->create([]);
        $this->assertInstanceOf(Topics::class, $topic);
    }
    public function test_topic_get_all(){
        factory(Topics::class, 5)->create();
        $topic = new TopicsRepository(new Topics());
        $data = $topic->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }
    public function test_show_topic()
    {
        $topic = factory(Topics::class)->create();
        $topicRepo = new TopicsRepository(new Topics());
        $found =  $topicRepo->find($topic->id);
        $this->assertInstanceOf(Topics::class, $found);
        $this->assertEquals($found->name,$topic->name);
        $this->assertEquals($found->rol,$topic->rol);
    }

    public function test_update_topic()
    {
        $topic =factory(Topics::class)->create();
        $data = [
            'name' => $this->faker->word(),
        ];

        $topicRepo = new TopicsRepository($topic);
        $update = $topicRepo->update($data);

        $this->assertTrue($update);

    }

    public function test_delete_topic()
    {
        $topic = factory(Topics::class)->create();
        $topicRepo = new TopicsRepository($topic);
        $delete = $topicRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_show_topic_exception()
    {
        $this->expectException(NotFoundException::class);
        $topicRepo = new TopicsRepository(new Topics());
        $topicRepo->find(28374);
    }
    public function test_update_topic_exception()
    {
        $this->expectException(UpdateException::class);
        $topic = factory(Topics::class)->create();
        $topicRepo = new TopicsRepository($topic);
        $data = ['title'=>null];
        $topicRepo->update($data);
    }

    public function test_topic_delete_null()
    {
        $topicRepo = new TopicsRepository(new Topics());
        $delete = $topicRepo->delete();
        $this->assertNull($delete);

    }


}
