<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Repositories\CreditsRepository;
use App\Models\Credits;
use Tests\TestCase;


class CreditsUserTest extends TestCase
{
    protected $userId;

    public function test_create_credits()
    {
        $repo = new CreditsRepository(New Credits());
        $data = factory(Credits::class)->create();
        $credit = $repo->create($data->toArray());
        $this->assertInstanceOf(Credits::class, $credit);
        $this->assertEquals($data->name,$credit->name);
        $this->assertEquals($data->date,$credit->date);

    }
    public function test_create_credits_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new CreditsRepository(New Credits());
        $credit = $repo->create([]);
        $this->assertInstanceOf(Credits::class, $credit);
    }
    public function test_credits_get_all(){
        factory(Credits::class, 5)->create();
        $credits = new CreditsRepository(new Credits());
        $data = $credits->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }
}
