<?php

namespace Tests\Unit\Marketplace;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\MarketplaceCategory;

use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository;

class MarketplaceCategoryTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;


    public function test_all_market_place_category(){
        factory(MarketplaceCategory::class,5)->create();
        $dataAll = new MarketplaceCategoryRepository(new MarketplaceCategory());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function test_create_market_place_category()
    {
        $data = [
            'name' => $this->faker->title,
             'description' => $this->faker->paragraph()
        ];
        $marketplace_category_repo = new MarketplaceCategoryRepository(new MarketplaceCategory());
        $marketplace_category = $marketplace_category_repo->create($data);
        $this->assertInstanceOf(MarketplaceCategory::class, $marketplace_category);
        $this->assertEquals($data['name'], $marketplace_category->name);
        $this->assertEquals($data['description'], $marketplace_category->description);
    }

    public function test_show_market_place_category()
    {
        $marketplace_category = factory(MarketplaceCategory::class)->create();
        $marketplace_category_repo = new MarketplaceCategoryRepository(new MarketplaceCategory());
        $found =  $marketplace_category_repo->find($marketplace_category->id);
        $this->assertInstanceOf(MarketplaceCategory::class, $found);
        $this->assertEquals($found->name,$marketplace_category->name);
        $this->assertEquals($found->description,$marketplace_category->description);
    }

    public function test_update_market_place_category()
    {
        $market_place = factory(MarketplaceCategory::class)->create();

        $data = [
            'name' => $this->faker->title,
             'description' => $this->faker->paragraph()
        ];

        $marketplace_category_repo = new MarketplaceCategoryRepository($market_place);
        $update = $marketplace_category_repo->update($data);
        $this->assertTrue($update);
        $this->assertEquals($data['name'], $market_place->name);
        $this->assertEquals($data['description'], $market_place->description);
    }


    public function test_delete_market_place_category()
    {
        $marketplace_category = factory(MarketplaceCategory::class)->create();
        $marketplace_category_repo = new MarketplaceCategoryRepository($marketplace_category);
        $delete = $marketplace_category_repo->delete();
        $this->assertTrue($delete);
    }


    public function test_create_market_place_category_exception()
    {
        $this->expectException(CreateException::class);
        $marketplace_category_repo = new MarketplaceCategoryRepository(new MarketplaceCategory());
        $marketplace_category_repo->create([]);
    }

    public function test_show_market_place_category_exception()
    {
        $this->expectException(NotFoundException::class);
        $marketplace_category_repo = new MarketplaceCategoryRepository(new MarketplaceCategory());
        $marketplace_category_repo->find(28374);
    }

    public function test_update_market_place_category_exception()
    {
        $this->expectException(UpdateException::class);
        $marketplace_category = factory(MarketplaceCategory::class)->create();
        $marketplace_category_repo =  new MarketplaceCategoryRepository($marketplace_category);
        $data = ['name'=>null];
        $marketplace_category_repo->update($data);
    }

    public function test_market_place_category_delete_null()
    {
        $marketplace_category_repo = new MarketplaceCategoryRepository(new MarketplaceCategory());
        $delete = $marketplace_category_repo->delete();
        $this->assertNull($delete);

    }

}
