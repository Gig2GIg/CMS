<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\CreditsRepository;
use App\Models\Credits;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;


class CreditsUserTest extends TestCase
{
    protected $userId;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create();
        $this->userId = $user->id;
    }

    public function test_create_credits()
    {
        $repo = new CreditsRepository(New Credits());
        $data = factory(Credits::class)->create(['user_id'=>$this->userId]);
        $credit = $repo->create($data->toArray());
        $this->assertInstanceOf(Credits::class, $credit);
        $this->assertEquals($data->name,$credit->name);
        $this->assertEquals($data->rol,$credit->rol);

    }
    public function test_create_credits_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new CreditsRepository(New Credits());
        $credit = $repo->create([]);
        $this->assertInstanceOf(Credits::class, $credit);
    }
    public function test_credits_get_all(){
        factory(Credits::class, 5)->create(['user_id'=>$this->userId]);
        $credits = new CreditsRepository(new Credits());
        $data = $credits->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }
    public function test_show_credit()
    {
        $credit = factory(Credits::class)->create(['user_id'=>$this->userId]);
        $creditRepo = new CreditsRepository(new Credits());
        $found =  $creditRepo->find($credit->id);
        $this->assertInstanceOf(Credits::class, $found);
        $this->assertEquals($found->name,$credit->name);
        $this->assertEquals($found->rol,$credit->rol);
    }

    public function test_update_credits()
    {
        $credit =factory(Credits::class)->create(['user_id'=>$this->userId]);
        $data = [
            'rol' => $this->faker->word(),
            'name' => $this->faker->words(3,1),
        ];

        $creditsRepo = new CreditsRepository($credit);
        $update = $creditsRepo->update($data);

        $this->assertTrue($update);

    }

    public function test_delete_credits()
    {
        $credit = factory(Credits::class)->create(['user_id'=>$this->userId]);
        $creditRepo = new CreditsRepository($credit);
        $delete = $creditRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_show_credits_exception()
    {
        $this->expectException(NotFoundException::class);
        $creditRepo = new CreditsRepository(new Credits());
        $creditRepo->find(28374);
    }
    public function test_update_credits_exception()
    {
        $this->expectException(UpdateException::class);
        $credits = factory(Credits::class)->create(['user_id'=>$this->userId]);
        $creditRepo = new CreditsRepository($credits);
        $data = ['name'=>null];
        $creditRepo->update($data);
    }

    public function test_credit_delete_null()
    {
        $creditRepo = new CreditsRepository(new Credits());
        $delete = $creditRepo->delete();
        $this->assertNull($delete);

    }

    public function test_create_credits_e()
    {
        $this->expectException(QueryException::class);
        $repo = new CreditsRepository(New Credits());
        $data = factory(Credits::class)->create();
        $credit = $repo->create($data->toArray());


    }

}
