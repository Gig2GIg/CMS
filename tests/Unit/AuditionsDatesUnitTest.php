<?php

namespace Tests\Unit;

use App\Http\Repositories\AuditionsDatesRepository;
use App\Models\AuditionsDate;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionsDatesUnitTest extends TestCase
{
    public function test_create_auditionsdates()
    {
        $data = factory(AuditionsDate::class)->create();
        $auditionsdatesRepo = new AuditionsDatesRepository(new AuditionsDate());
        $auditionsdates = $auditionsdatesRepo->create($data->toArray());
        $this->assertInstanceOf(AuditionsDate::class, $auditionsdates);
        $this->assertEquals($data['type'], $auditionsdates->type);
        $this->assertEquals($data['to'], $auditionsdates->to);
        $this->assertEquals($data['from'], $auditionsdates->from);
    }

    public function test_edit_auditionsdates()
    {
        $data = factory(auditionsdates::class)->create();
        $dataUpdate = [
            'title' => $this->faker->title(),
            'description' => $this->faker->paragraph(),
        ];
        $auditionsdatesRepo = new auditionsdatesRepository($data);
        $auditionsdates = $auditionsdatesRepo->update($dataUpdate);
        $this->assertTrue($auditionsdates);


    }

    public function test_delete_auditionsdates()
    {
        $data = factory(auditionsdates::class)->create();
        $auditionsdatesRepo = new auditionsdatesRepository($data);
        $delete = $auditionsdatesRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_auditionsdates()
    {
        $data = factory(auditionsdates::class)->create();
        $auditionsdatesRepo = new auditionsdatesRepository(new auditionsdates());
        $found = $auditionsdatesRepo->find($data->id);
        $this->assertInstanceOf(auditionsdates::class,$found);
        $this->assertEquals($found->title,$data->title);
        $this->assertEquals($found->description,$data->description);
    }

    public function test_all_auditionsdates()
    {
        factory(auditionsdates::class,5)->create();
        $auditionsdates = new auditionsdatesRepository(new auditionsdates());
        $data = $auditionsdates->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_auditionsdates_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new auditionsdatesRepository(new auditionsdates());
        $userRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $auditionsdates = new auditionsdatesRepository(new auditionsdates());
        $auditionsdates->find(2345);
    }

    public function test_update_auditionsdates_exception()
    {
        $this->expectException(UpdateException::class);
        $auditionsdates = factory(auditionsdates::class)->create();
        $auditionsdatesRepo = new auditionsdatesRepository($auditionsdates);
        $data = ['name'=>null];
        $auditionsdatesRepo->update($data);
    }

    public function test_delete_auditionsdates_null_exception()
    {

        $auditionsdatesRepo = new auditionsdatesRepository(new auditionsdates());
        $delete = $auditionsdatesRepo->delete();
        $this->assertNull($delete);
    }
}
