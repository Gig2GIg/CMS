<?php

namespace Tests\Unit\Cms\Marketplace;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\SkillSuggestion;
use App\Http\Repositories\SkillSuggestionsRepository;

class SkillSuggestionsUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testAllSkillSuggestion(){
        factory(SkillSuggestion::class,5)->create();
        $dataAll = new SkillSuggestionsRepository(new SkillSuggestion());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function testCreateSkillSuggestion()
    {
        $data = [
            'name' => $this->faker->name
        ];

        $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());
        $skillSuggestion = $skillSuggestionRepo->create($data);
     
        $this->assertInstanceOf(SkillSuggestion::class, $skillSuggestion);
        $this->assertEquals($data['name'], $skillSuggestion->name);
    }

    public function testShowSkillSuggestion()
    {
        $skillSuggestion = factory(SkillSuggestion::class)->create();
        $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());
        $found =  $skillSuggestionRepo->find($skillSuggestion->id);

        $this->assertInstanceOf(SkillSuggestion::class, $found);
        $this->assertEquals($found->name,$skillSuggestion->name);
    }

    public function testUpdateSkillSuggestion()
    {
        $skillSuggestion = factory(SkillSuggestion::class)->create();

        $data = [
            'name' => $this->faker->name,
        ];

        $skillSuggestionRepo = new SkillSuggestionsRepository($skillSuggestion);
        $update = $skillSuggestionRepo->update($data);
        $this->assertTrue($update);
        $this->assertEquals($data['name'], $skillSuggestion->name);
    }


    public function testDeleteSkillSuggestion()
    {
        $skillSuggestion = factory(SkillSuggestion::class)->create();
        $skillSuggestionRepo = new SkillSuggestionsRepository($skillSuggestion);
        $delete = $skillSuggestionRepo->delete();
        $this->assertTrue($delete);
    }


    public function tesCreateException()
    {
        $this->expectException(CreateException::class);
        $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());
        $skillSuggestionRepo->create([]);
    }

    public function testShowException()
    {
        $this->expectException(NotFoundException::class);
        $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());
        $skillSuggestionRepo->find(282374);
    }

    public function testUpdateException()
    {
        $this->expectException(UpdateException::class);
        $skillSuggestion = factory(SkillSuggestion::class)->create();
        $skillSuggestionRepo =  new SkillSuggestionsRepository($skillSuggestion);
        $data = ['name'=>null];
        $skillSuggestionRepo->update($data);
    }

    public function testDeleteNull()
    {
        $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());
        $delete = $skillSuggestionRepo->delete();
        $this->assertNull($delete);

    }

    public function testSearchByName()
    {
        $skillSuggestion = factory(SkillSuggestion::class)->create();
        $value = $skillSuggestion->name;
        $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());
        $result = $skillSuggestionRepo->search_by_name($value);
        
        $this->assertEquals($value, $result[0]->name);

    }
}
