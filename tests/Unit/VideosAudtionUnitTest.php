<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AuditionVideosRepository;
use App\Models\AuditionVideos;
use App\Models\Resources;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideosAudtionUnitTest extends TestCase
{
    protected $userId;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create();
        $this->userId = $user->id;
    }

    public function test_create_audition_video()
    {
        $contributors = factory(User::class)->create();
        $repo = new AuditionVideosRepository(New AuditionVideos());
        $resource = new Reposi
        $data = factory(AuditionVideos::class)->create([
            'user_id'=>$this->userId,
            'contributor_id'=>$contributors->id,
        ]);
        $skill_user = $repo->create($data->toArray());
        $this->assertInstanceOf(AuditionVideos::class, $skill_user);
        $this->assertEquals($data->name,$skill_user->name);
        $this->assertEquals($data->rol,$skill_user->rol);

    }
    public function test_create_skill_user_users_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new AuditionVideosRepository(New AuditionVideos());
        $skill_user = $repo->create([]);
        $this->assertInstanceOf(AuditionVideos::class, $skill_user);
    }
    public function test_skill_user_get_all(){
        factory(AuditionVideos::class, 5)->create([
            'user_id'=>$this->userId,
            'skills_id'=>$this->skillId
        ]);
        $skill_user = new AuditionVideosRepository(new AuditionVideos());
        $data = $skill_user->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }
    public function test_show_skill_user()
    {
        $skill_user = factory(AuditionVideos::class)->create([
            'user_id'=>$this->userId,
            'skills_id'=>$this->skillId
        ]);
        $skill_userRepo = new AuditionVideosRepository(new AuditionVideos());
        $found =  $skill_userRepo->find($skill_user->id);
        $this->assertInstanceOf(AuditionVideos::class, $found);
        $this->assertEquals($found->name,$skill_user->name);
        $this->assertEquals($found->rol,$skill_user->rol);
    }



    public function test_delete_skill_user()
    {
        $skill_user = factory(AuditionVideos::class)->create([
            'user_id'=>$this->userId,
            'skills_id'=>$this->skillId
        ]);
        $skill_userRepo = new AuditionVideosRepository($skill_user);
        $delete = $skill_userRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_show_skill_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $skill_userRepo = new AuditionVideosRepository(new AuditionVideos());
        $skill_userRepo->find(28374);
    }


    public function test_skill_user_delete_null()
    {
        $skill_userRepo = new AuditionVideosRepository(new AuditionVideos());
        $delete = $skill_userRepo->delete();
        $this->assertNull($delete);

    }
}
