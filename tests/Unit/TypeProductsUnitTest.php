<?php

namespace Tests\Unit\Cms\Marketplace;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\TypeProduct;
use App\Http\Repositories\TypeProductsRepository;

class TypeProductsUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testAllTypeProduct(){
        factory(TypeProduct::class,5)->create();
        $dataAll = new TypeProductsRepository(new TypeProduct());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function testCreateTypeProduct()
    {
        $data = [
            'name' => $this->faker->name
        ];

        $TypeProductRepo = new TypeProductsRepository(new TypeProduct());
        $typeProduct = $TypeProductRepo->create($data);
     
        $this->assertInstanceOf(TypeProduct::class, $typeProduct);
        $this->assertEquals($data['name'], $typeProduct->name);
    }

    public function testShowTypeProduct()
    {
        $typeProduct = factory(TypeProduct::class)->create();
        $TypeProductRepo = new TypeProductsRepository(new TypeProduct());
        $found =  $TypeProductRepo->find($typeProduct->id);

        $this->assertInstanceOf(TypeProduct::class, $found);
        $this->assertEquals($found->name,$typeProduct->name);
    }

    public function testUpdateTypeProduct()
    {
        $typeProduct = factory(TypeProduct::class)->create();

        $data = [
            'name' => $this->faker->name,
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
