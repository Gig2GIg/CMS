<?php
namespace Tests\Unit\Cms;

use App\Models\Admin;
use App\Models\TypeProduct;
use Tests\TestCase;

use App\Models\User;
use App\Models\UserDetails;

class TypeProductsControllerUnitTest extends TestCase
{

    protected $token;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(Admin::class)->create([
                'email' => 'cms@test.com',
                'password' => bcrypt('123456')]
        );

        $response = $this->post('api/admin/login', [
            'email' => 'cms@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('token');
    }

    public function testCreateTypeProduct201()
    {
        $data = [
            'name' => "Some text here"
        ];

        $response = $this->json('POST',
            'api/cms/type-products/create?token=' . $this->token,
            $data);

        $response->assertStatus(201);
    }

    public function testCreateTypeProduct422()
    {
        $response = $this->json('POST',
            'api/cms/type-products/create?token=' . $this->token,
            []);
        $response->assertStatus(422);

    }

    public function testShowTypeProduct200()
    {
        $typeProduct = factory(TypeProduct::class)->create();

        $response = $this->json('GET',
        'api/cms/type-products/show/' . $typeProduct->id . '?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function testShowAll200()
    {
        $typeProduct = factory(TypeProduct::class, 5)->create();

        $response = $this->json('GET',
            'api/cms/type-products?token=' . $this->token);
        $response->assertStatus(200);
    }


    public function testUpdateTypeProduct()
    {
        $data = [
            'name' => 'Some name here'
        ];
        $typeProduct = factory(TypeProduct::class)->create();

        $response = $this->json('PUT',
            'api/cms/type-products/update/' .$typeProduct->id.'?token=' . $this->token,
            $data);
        $response->assertStatus(204);

    }


    public function testDeleteTypeProduct()
    {

        $typeProduct = factory(TypeProduct::class)->create();

        $response = $this->json('DELETE',
            'api/cms/type-products/delete/' .$typeProduct->id.'?token=' . $this->token);
        $response->assertStatus(204);

    }

}

