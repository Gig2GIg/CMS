<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Repositories\FinalCastRepository;
use App\Models\Auditions;
use App\Models\FinalCast;
use App\Models\Roles;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinalCastTest extends TestCase
{
    public function test_create_final_cast()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id'=>$user->id
        ]);
        $rol = factory(Roles::class)->create([
           'auditions_id'=>$audition->id,
        ]);
        $data = [
            'audition_id'=>$audition->id,
            'performer_id'=>$user->id,
            'rol_id'=>$rol->id
        ];

        $repo = new FinalCastRepository(new FinalCast());
        $create = $repo->create($data);
        $this->assertInstanceOf(FinalCast::class, $create);
    }

    public function test_create_final_cast_exception()
    {
        $this->expectException(CreateException::class);

        $data = [

        ];

        $repo = new FinalCastRepository(new FinalCast());
        $create = $repo->create($data);
        $this->assertInstanceOf(FinalCast::class, $create);
    }
}
