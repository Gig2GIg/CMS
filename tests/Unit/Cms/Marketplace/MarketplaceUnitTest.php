<?php

namespace Tests\Unit\Cms\Marketplace;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\Marketplace;
use App\Models\MarketplaceCategory;
use App\Http\Repositories\Marketplace\MarketplaceRepository;

class MarketplaceUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $marketplace_category_id;

    public function setUp(): void
    {
        parent::setUp();
        $category = factory(MarketplaceCategory::class)->create();
        $this->marketplace_category_id = $category->id;
    }

    public function test_all_market_place(){
        factory(Marketplace::class,5)->create();
        $dataAll = new MarketplaceRepository(new Marketplace());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function test_create_market_place()
    {
        $data = [
            'address' => $this->faker->address,
            'title' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'services' => $this->faker->text($maxNbChars = 100),
            'marketplace_category_id' => $this->marketplace_category_id
        ];
        $marketplace_repo = new MarketplaceRepository(new Marketplace());
        $marketplace = $marketplace_repo->create($data);
     
        $this->assertInstanceOf(Marketplace::class, $marketplace);
        $this->assertEquals($data['address'], $marketplace->address);
        $this->assertEquals($data['title'], $marketplace->title);
        $this->assertEquals($data['phone_number'], $marketplace->phone_number);
        $this->assertEquals($data['email'], $marketplace->email);
        $this->assertEquals($data['services'], $marketplace->services);

        $image = [
            'url' => $this->faker->imageUrl($width = 640, $height = 480, 'cats'),
            'type' => '3'
        ];
       
        $image_result = $marketplace->image()->create($image);
        $this->assertEquals($image['url'], $image_result->url);
        $this->assertEquals($image['type'], $image_result->type);
    }

    public function test_show_market_place()
    {
        $marketplace = factory(Marketplace::class)->create();
        $marketplace_repo = new MarketplaceRepository(new Marketplace());
        $found =  $marketplace_repo->find($marketplace->id);

        $this->assertInstanceOf(Marketplace::class, $found);
        $this->assertEquals($found->address,$marketplace->address);
        $this->assertEquals($found->title,$marketplace->title);
    }

    public function test_update_market_place()
    {
        $marketplace = factory(Marketplace::class)->create();

        $data = [
            'address' => $this->faker->address,
            'title' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'services' => $this->faker->text($maxNbChars = 100),
            'marketplace_category_id' => $this->marketplace_category_id
        ];

        $marketplace_repo = new MarketplaceRepository($marketplace);
        $update = $marketplace_repo->update($data);
        $this->assertTrue($update);
        $this->assertEquals($data['address'], $marketplace->address);
        $this->assertEquals($data['title'], $marketplace->title);
        $this->assertEquals($data['phone_number'], $marketplace->phone_number);
        $this->assertEquals($data['email'], $marketplace->email);
        $this->assertEquals($data['services'], $marketplace->services);
    }


    public function test_delete_market_place()
    {
        $marketplace = factory(Marketplace::class)->create();
        $marketplace_repo = new MarketplaceRepository($marketplace);
        $delete = $marketplace_repo->delete();
        $this->assertTrue($delete);
    }


    public function test_create_market_place_exception()
    {
        $this->expectException(CreateException::class);
        $marketplace_repo = new MarketplaceRepository(new Marketplace());
        $marketplace_repo->create([]);
    }

    public function test_show_market_place_exception()
    {
        $this->expectException(NotFoundException::class);
        $marketplace_repo = new MarketplaceRepository(new Marketplace());
        $marketplace_repo->find(282374);
    }

    public function test_update_market_place_exception()
    {
        $this->expectException(UpdateException::class);
        $marketplace = factory(Marketplace::class)->create();
        $marketplace_repo =  new MarketplaceRepository($marketplace);
        $data = ['title'=>null];
        $marketplace_repo->update($data);
    }

    public function test_market_place_delete_null()
    {
        $marketplace_repo = new MarketplaceRepository(new Marketplace());
        $delete = $marketplace_repo->delete();
        $this->assertNull($delete);

    }

    public function test_search_by_title()
    {
        $marketplace = factory(Marketplace::class)->create();
        $value = $marketplace->title;
        $marketplace_repo = new MarketplaceRepository(new Marketplace());
        $result = $marketplace_repo->search_by_title($value );
        
        $this->assertEquals($value, $result[0]->title);

    }
}
