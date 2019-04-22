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

        $TypeProductRepo = new TypeProductsRepository($typeProduct);
        $update = $TypeProductRepo->update($data);
        $this->assertTrue($update);
        $this->assertEquals($data['name'], $typeProduct->name);
    }


    public function testDeleteTypeProduct()
    {
        $typeProduct = factory(TypeProduct::class)->create();
        $TypeProductRepo = new TypeProductsRepository($typeProduct);
        $delete = $TypeProductRepo->delete();
        $this->assertTrue($delete);
    }


    public function tesCreateException()
    {
        $this->expectException(CreateException::class);
        $TypeProductRepo = new TypeProductsRepository(new TypeProduct());
        $TypeProductRepo->create([]);
    }

    public function testShowException()
    {
        $this->expectException(NotFoundException::class);
        $TypeProductRepo = new TypeProductsRepository(new TypeProduct());
        $TypeProductRepo->find(282374);
    }

    public function testUpdateException()
    {
        $this->expectException(UpdateException::class);
        $typeProduct = factory(TypeProduct::class)->create();
        $TypeProductRepo =  new TypeProductsRepository($typeProduct);
        $data = ['name'=>null];
        $TypeProductRepo->update($data);
    }

    public function testDeleteNull()
    {
        $TypeProductRepo = new TypeProductsRepository(new TypeProduct());
        $delete = $TypeProductRepo->delete();
        $this->assertNull($delete);

    }

    public function testSearchByName()
    {
        $typeProduct = factory(TypeProduct::class)->create();
        $value = $typeProduct->name;
        $TypeProductRepo = new TypeProductsRepository(new TypeProduct());
        $result = $TypeProductRepo->search_by_name($value);
        
        $this->assertEquals($value, $result[0]->name);

    }
}
