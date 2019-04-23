<?php
namespace Tests\Unit\Cms;

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
        $user = factory(User::class)->create([
                'email' => 'table@test.com',
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $userDetails = factory(UserDetails::class)->create([
            'type'=>1,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'table@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token'); 
    }

    public function testShowAll200()
    {
        $typeProduct = factory(TypeProduct::class, 5)->create();

        $response = $this->json('GET',
            'api/t/type-products?token=' . $this->token);
        $response->assertStatus(200);
    }

}

