<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\AuditionRepository;
use App\Models\Auditions;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionsUnitTest extends TestCase
{

    protected $user_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $this->user_id = $user->id;
    }

    public function test_create_auditions()
    {
        $data = factory(Auditions::class)->create(['user_id'=>$this->user_id]);

        $auditionsRepo = new AuditionRepository(new Auditions());
        $audition = $auditionsRepo->create($data->toArray());
        $this->assertInstanceOf(Auditions::class, $audition);
        $this->assertEquals($data['title'], $audition->title);
        $this->assertEquals($data['date'], $audition->date);
    }

    public function test_edit_auditions()
    {
        $data = factory(Auditions::class)->create(['user_id'=>$this->user_id]);
        $dataUpdate = [
            'title' => 'Aladino',
            'location' => 'SAN SALVADOR',
            'description' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
        ];
        $auditionsRepo = new AuditionRepository($data);
        $audition = $auditionsRepo->update($dataUpdate);
        $this->assertTrue($audition);


    }

    public function test_delete_auditions()
    {
        $data = factory(Auditions::class)->create(['user_id'=>$this->user_id]);
        $auditionsRepo = new AuditionRepository($data);
        $delete = $auditionsRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_audition()
    {
        $data = factory(Auditions::class)->create(['user_id'=>$this->user_id]);
        $auditionRepo = new AuditionRepository(new Auditions());
        $found = $auditionRepo->find($data->id);
        $this->assertInstanceOf(Auditions::class, $found);
        $this->assertEquals($found->union, $data->union);
        $this->assertEquals($found->date, $data->date);
    }

    public function test_all_auditions()
    {
        factory(Auditions::class, 5)->create(['user_id'=>$this->user_id]);
        $auditions = new AuditionRepository(new Auditions());
        $data = $auditions->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_audition_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new AuditionRepository(new Auditions());
        $userRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $audition = new AuditionRepository(new Auditions());
        $audition->find(2345);
    }

    public function test_update_audition_exception()
    {
        $this->expectException(UpdateException::class);
        $audition = factory(Auditions::class)->create(['user_id'=>$this->user_id]);
        $auditionRepo = new AuditionRepository($audition);
        $data = ['title' => null];
        $auditionRepo->update($data);
    }

    public function test_delete_audition_null_exception()
    {

        $auditionRepo = new AuditionRepository(new Auditions());
        $delete = $auditionRepo->delete();
        $this->assertNull($delete);
    }

    public function test_all_media_by_image()
    {
        $audition =  factory(Auditions::class)->create(['user_id'=>$this->user_id]);

        $audition->media()->create(['url' => "http://baumbach.com/repellat-voluptatem-excepturi-quam-et-dolores-officiis-eius", 'type' => 'audio','name'=>'test']);
        $audition->media()->create(['url' => "http://baumbach.com/repellat-voluptatem-excepturi-quam-et-dolores-officiis-eius", 'type' => 'video','name'=>'test']);
        $audition->media()->create(['url' => "http://baumbach.com/repellat-voluptatem-excepturi-quam-et-dolores-officiis-eius", 'type' => 'doc','name'=>'test']);
        $audition->media()->create(['url' => "http://baumbach.com/repellat-voluptatem-excepturi-quam-et-dolores-officiis-eius", 'type' => 'image','name'=>'test']);

        $audition_repo = new AuditionRepository($audition);
        $data = $audition_repo->findMediaByParams('image');
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 0);

        $data = $audition_repo->findMediaByParams('video');
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 0);

        $data = $audition_repo->findMediaByParams('doc');
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 0);

        $data = $audition_repo->findMediaByParams('audio');
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 0);
    }

}
