<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\FinalCastRepository;
use App\Models\Auditions;
use App\Models\FinalCast;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinalCastTest extends TestCase
{
    public function test_create_final_cast()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id,
        ]);
        $data = [
            'audition_id' => $audition->id,
            'performer_id' => $user->id,
            'rol_id' => $rol->id
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

    public function test_get_list_final_cast_by_audition()
    {
        $audition = factory(Auditions::class)->create(['user_id'=>factory(User::class)->create()->id]);
        $roles = collect(factory(Roles::class,10)->create(['auditions_id'=>$audition->id]));
        factory(FinalCast::class,10)->create([
            'audition_id' => $audition->id,
            'performer_id' => factory(User::class)->create()->id,
            'rol_id' => $roles->random()->id
        ]);

        $repo = new FinalCastRepository(new FinalCast());
        $list = $repo->findbyparam('audition_id',$audition->id)->get();

        $this->assertTrue($list->count() > 0);
    }

    public function test_get_list_final_cast_by_audition_not_data(){

        $audition = factory(Auditions::class)->create(['user_id'=>factory(User::class)->create()->id]);
        $repo = new FinalCastRepository(new FinalCast());
        $list = $repo->findbyparam('audition_id',$audition->id)->get();

        $this->assertTrue($list->count() === 0);
    }

    public function test_final_cast_by_id(){
        $audition = factory(Auditions::class)->create(['user_id'=>factory(User::class)->create()->id]);
        $roles = collect(factory(Roles::class,10)->create(['auditions_id'=>$audition->id]));
        $cast = factory(FinalCast::class)->create([
            'audition_id' => $audition->id,
            'performer_id' => factory(User::class)->create()->id,
            'rol_id' => $roles->random()->id
        ]);
        $repo = new FinalCastRepository(new FinalCast());
        $data = $repo->find($cast->id);
        $this->assertInstanceOf(FinalCast::class, $data);
    }

    public function test_final_cast_by_id_exception(){
        $this->expectException(NotFoundException::class);
        $repo = new FinalCastRepository(new FinalCast());
        $data = $repo->find(99);
        $this->assertInstanceOf(FinalCast::class, $data);
    }

    public function test_update_final_cast(){
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id,
        ]);
        $cast = factory(FinalCast::class)->create([
            'audition_id' => $audition->id,
            'performer_id' => factory(User::class)->create()->id,
            'rol_id' => $rol->id
        ]);
        $repo = new FinalCastRepository(new FinalCast());
        $data = $repo->find($cast->id);
        $update = $data->update([
            'rol_id'=>factory(Roles::class)->create(['auditions_id'=>$audition->id])->id
        ]);

        $this->assertTrue($update);
    }

    public function test_update_final_cast_exception(){
        $this->expectException(QueryException::class);
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id,
        ]);
        $cast = factory(FinalCast::class)->create([
            'audition_id' => $audition->id,
            'performer_id' => factory(User::class)->create()->id,
            'rol_id' => $rol->id
        ]);
        $repo = new FinalCastRepository(new FinalCast());
        $data = $repo->find($cast->id);
        $update = $data->update([
            'rol_id'=>2,
        ]);

        $this->assertTrue($update);
    }

    public function test_delete_final_cast(){
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id,
        ]);
        $cast = factory(FinalCast::class)->create([
            'audition_id' => $audition->id,
            'performer_id' => factory(User::class)->create()->id,
            'rol_id' => $rol->id
        ]);
        $repo = new FinalCastRepository(new FinalCast());
        $data = $repo->find($cast->id);
        $delete = $data->delete();

        $this->assertTrue($delete);
    }

}
