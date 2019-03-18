<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\AuditionsDatesRepository;
use App\Models\Auditions;
use App\Models\AuditionsDate;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionsDatesUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $audition_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id'=>$user->id]);
        $this->audition_id = $audition->id;
    }

    public function test_create_auditionsdates()
    {

        $data = factory(AuditionsDate::class)->create(['audition_id'=>$this->audition_id]);

        $auditionsdatesRepo = new AuditionsDatesRepository(new AuditionsDate());
        $auditionsdates = $auditionsdatesRepo->create($data->toArray());
        $this->assertInstanceOf(AuditionsDate::class, $auditionsdates);
        $this->assertEquals($data['type'], $auditionsdates->type);
        $this->assertEquals($data['to'], $auditionsdates->to);
        $this->assertEquals($data['from'], $auditionsdates->from);
    }

    public function test_edit_auditionsdates()
    {
        $data = factory(AuditionsDate::class)->create(['audition_id'=>$this->audition_id]);
        $dataUpdate = [
            'to' => $this->faker->date(),
            'type' => $this->faker->numberBetween(1,2),
        ];
        $auditionsdatesRepo = new AuditionsDatesRepository($data);
        $auditionsdates = $auditionsdatesRepo->update($dataUpdate);
        $this->assertTrue($auditionsdates);


    }

    public function test_delete_auditionsdates()
    {
        $data = factory(AuditionsDate::class)->create(['audition_id'=>$this->audition_id]);
        $auditionsdatesRepo = new auditionsdatesRepository($data);
        $delete = $auditionsdatesRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_auditionsdates()
    {
        $data = factory(AuditionsDate::class)->create(['audition_id'=>$this->audition_id]);
        $auditionsdatesRepo = new auditionsdatesRepository(new AuditionsDate());
        $found = $auditionsdatesRepo->find($data->id);
        $this->assertInstanceOf(AuditionsDate::class,$found);
        $this->assertEquals($found->from,$data->from);
        $this->assertEquals($found->to,$data->to);
    }

    public function test_all_auditionsdates()
    {
        factory(AuditionsDate::class,5)->create(['audition_id'=>$this->audition_id]);
        $auditionsdates = new auditionsdatesRepository(new AuditionsDate());
        $data = $auditionsdates->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_auditionsdates_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new AuditionsDatesRepository(new AuditionsDate());
        $userRepo->create([]);
    }

    public function test_show_auditionsdate_exception()
    {
        $this->expectException(NotFoundException::class);
        $auditionsdates = new AuditionsDatesRepository(new AuditionsDate());
        $auditionsdates->find(2345);
    }

    public function test_update_auditionsdates_exception()
    {
        $this->expectException(UpdateException::class);
        $auditionsdates = factory(AuditionsDate::class)->create(['audition_id'=>$this->audition_id]);
        $auditionsdatesRepo = new AuditionsDatesRepository($auditionsdates);
        $data = ['to'=>null];
        $auditionsdatesRepo->update($data);
    }

    public function test_delete_auditionsdates_null_exception()
    {

        $auditionsdatesRepo = new AuditionsDatesRepository(new AuditionsDate());
        $delete = $auditionsdatesRepo->delete();
        $this->assertNull($delete);
    }
}
