<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionContributorsUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $audition_id;

    public function setUp(): void
    {
        parent::setUp();
        $audition  = factory(Auditions::class)->create();
        $this->audition_id = $audition->id;
    }

    public function test_create_auditionsContributors()
    {

        $data = factory(AuditionContributors::class)->create(['audition_id'=>$this->audition_id]);

        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $auditionsContributors = $auditionsContributorsRepo->create($data->toArray());
        $this->assertInstanceOf(AuditionContributors::class, $auditionsContributors);
        $this->assertEquals($data['email'], $auditionsContributors->email);
     
    }



    public function test_delete_auditionsContributors()
    {
        $data = factory(AuditionContributors::class)->create(['audition_id'=>$this->audition_id]);
        $auditionsContributorsRepo = new AuditionContributorsRepository($data);
        $delete = $auditionsContributorsRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_auditionsContributors()
    {
        $data = factory(AuditionContributors::class)->create(['audition_id'=>$this->audition_id]);
        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $found = $auditionsContributorsRepo->find($data->id);
        $this->assertInstanceOf(AuditionContributors::class,$found);
        $this->assertEquals($found->email,$data->email);

    }

    public function test_all_auditionsContributors()
    {
        factory(AuditionContributors::class,5)->create(['audition_id'=>$this->audition_id]);
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

    public function test_update_auditionsContributors_exception()
    {
        $this->expectException(UpdateException::class);
        $auditionsContributors = factory(AuditionContributors::class)->create(['audition_id'=>$this->audition_id]);
        $auditionsContributorsRepo = new AuditionContributorsRepository($auditionsContributors);
        $data = ['email'=>null];
        $auditionsContributorsRepo->update($data);
    }

    public function test_delete_auditionsContributors_null_exception()
    {

        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $delete = $auditionsContributorsRepo->delete();
        $this->assertNull($delete);
    }
}
