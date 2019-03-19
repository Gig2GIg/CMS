<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionContributorsUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $auditions_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $audition  = factory(Auditions::class)->create(['user_id'=>$user->id]);
        $this->auditions_id = $audition->id;
    }

    public function test_create_auditionsContributors()
    {
        $user = factory(User::class)->create();
        $data = factory(AuditionContributors::class)->create(['auditions_id'=>$this->auditions_id,'user_id'=>$user->id]);

        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $auditionsContributors = $auditionsContributorsRepo->create($data->toArray());
        $this->assertInstanceOf(AuditionContributors::class, $auditionsContributors);
        $this->assertEquals($data['email'], $auditionsContributors->email);
     
    }



    public function test_delete_auditionsContributors()
    {
        $user = factory(User::class)->create();
        $data = factory(AuditionContributors::class)->create(['auditions_id'=>$this->auditions_id,'user_id'=>$user->id]);
        $auditionsContributorsRepo = new AuditionContributorsRepository($data);
        $delete = $auditionsContributorsRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_auditionsContributors()
    {
        $user = factory(User::class)->create();
        $data = factory(AuditionContributors::class)->create(['auditions_id'=>$this->auditions_id,'user_id'=>$user->id]);
        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $found = $auditionsContributorsRepo->find($data->id);
        $this->assertInstanceOf(AuditionContributors::class,$found);
        $this->assertEquals($found->email,$data->email);

    }

    public function test_all_auditionsContributors()
    {
        $user = factory(User::class)->create();
        factory(AuditionContributors::class,5)->create(['auditions_id'=>$this->auditions_id,'user_id'=>$user->id]);
        $auditionsContributors = new AuditionContributorsRepository(new AuditionContributors());
        $data = $auditionsContributors->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_auditionsContributors_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new AuditionContributorsRepository(new AuditionContributors());
        $userRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $auditionsContributors = new AuditionContributorsRepository(new AuditionContributors());
        $auditionsContributors->find(2345);
    }



    public function test_delete_auditionsContributors_null_exception()
    {

        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $delete = $auditionsContributorsRepo->delete();
        $this->assertNull($delete);
    }
}
