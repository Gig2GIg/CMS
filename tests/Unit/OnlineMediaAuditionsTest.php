<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\OnlineMediaAudition;
use App\Models\User;
use Tests\TestCase;


class OnlineMediaAuditionsTest extends TestCase
{

    protected  $appointment_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id'=>$user->id]);
        $this->appointment_id =  factory(Appointments::class)->create(['auditions_id'=>$audition->id])->id;
    }

    public function test_create_media()
    {
        $data = factory(OnlineMediaAudition::class)->create([
            'appointment_id' => $this->appointment_id,
            'performer_id'=>factory(User::class)->create()->id,
            'type'=>1
        ]);
        $onlineMediaRepo = new MediaOnlineRepository(new OnlineMediaAudition());
        $online = $onlineMediaRepo->create($data->toArray());
        $this->assertInstanceOf(OnlineMediaAudition::class, $online);
        $this->assertEquals($data['type'], $online->type);
        $this->assertEquals($data['url'], $online->url);

    }

    public function test_edit_media_online()
    {
        $data = factory(OnlineMediaAudition::class)->create([
            'appointment_id' => $this->appointment_id,
            'performer_id'=>factory(User::class)->create()->id,
            'type'=>1
        ]);
        $onlineMediaRepo = new MediaOnlineRepository(new OnlineMediaAudition());
        $dataUpdate = [
            'url' => $this->faker->url(),
        ];

        $update = $onlineMediaRepo->find($data->id)->update($dataUpdate);
        $this->assertTrue($update);


    }

    public function test_delete_media()
    {
        $data = factory(OnlineMediaAudition::class)->create([
            'appointment_id' => $this->appointment_id,
            'performer_id'=>factory(User::class)->create()->id,
            'type'=>1
        ]);
        $mediRepo = new MediaOnlineRepository($data);
        $delete = $mediRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_media()
    {
        $data = factory(OnlineMediaAudition::class)->create([
            'appointment_id' => $this->appointment_id,
            'performer_id'=>factory(User::class)->create()->id,
            'type'=>'video'
        ]);
        $mediaRepo = new MediaOnlineRepository(new OnlineMediaAudition());
        $found = $mediaRepo->find($data->id);
        $this->assertInstanceOf(OnlineMediaAudition::class, $found);
        $this->assertEquals($found->type, $data->type);
        $this->assertEquals($found->url, $data->url);
    }


    public function test_find_media_by_param()
    {
       $user = factory(User::class)->create()->id;
        factory(OnlineMediaAudition::class,20)->create([
            'appointment_id' => $this->appointment_id,
            'performer_id'=>$user,
            'type'=>'video'
        ]);
        $mediaRepo = new MediaOnlineRepository(new OnlineMediaAudition());
        $found = $mediaRepo->findbyparam('performer_id',$user);
      $this->assertTrue($found->count() > 0);

    }

}
