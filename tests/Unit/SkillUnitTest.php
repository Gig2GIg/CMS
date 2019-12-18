<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\SkillsRepository;
use App\Models\Skills;
use Tests\TestCase;

class SkillUnitTest extends TestCase
{
    public function test_create_skill()
    {
        $repo = new SkillsRepository(New Skills());
        $data = factory(Skills::class)->create();
        $skill = $repo->create($data->toArray());
        $this->assertInstanceOf(Skills::class, $skill);
        $this->assertEquals($data->name,$skill->name);
        $this->assertEquals($data->rol,$skill->rol);

    }
    public function test_create_skill_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new SkillsRepository(New Skills());
        $skill = $repo->create([]);
        $this->assertInstanceOf(Skills::class, $skill);
    }
    public function test_skill_get_all(){
        factory(Skills::class, 5)->create();
        $skill = new SkillsRepository(new Skills());
        $data = $skill->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }
    public function test_show_skill()
    {
        $skill = factory(Skills::class)->create();
        $skillRepo = new SkillsRepository(new Skills());
        $found =  $skillRepo->find($skill->id);
        $this->assertInstanceOf(Skills::class, $found);
        $this->assertEquals($found->name,$skill->name);
        $this->assertEquals($found->rol,$skill->rol);
    }

    public function test_update_skill()
    {
        $skill =factory(Skills::class)->create();
        $data = [
            'name' => $this->faker->word(),
        ];

        $skillRepo = new SkillsRepository($skill);
        $update = $skillRepo->update($data);

        $this->assertTrue($update);

    }

    public function test_delete_skill()
    {
        $skill = factory(Skills::class)->create();
        $skillRepo = new SkillsRepository($skill);
        $delete = $skillRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_show_skill_exception()
    {
        $this->expectException(NotFoundException::class);
        $skillRepo = new SkillsRepository(new Skills());
        $skillRepo->find(28374);
    }
    public function test_update_skill_exception()
    {
        $this->expectException(UpdateException::class);
        $skill = factory(Skills::class)->create();
        $skillRepo = new SkillsRepository($skill);
        $data = ['name'=>null];
        $skillRepo->update($data);
    }

    public function test_skill_delete_null()
    {
        $skillRepo = new SkillsRepository(new Skills());
        $delete = $skillRepo->delete();
        $this->assertNull($delete);

    }


}
