<?php

namespace Tests\Unit\Notification;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\NotificationUserSetting;
use App\Models\Notification;
use App\Models\User;
use App\Http\Repositories\Notification\NotificationUserSettingRepository;

class NotificationUserSettingUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $user_id;
    protected $notification_id;
    protected $data;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $this->user_id = $user->id;
        $type = 'app';
        $notification = factory(Notification::class)->create(['type' => $type]);
        $this->notification_id = $notification->id;

        $data = [
            'notification_id'=>$notification->id,
            'user_id'=>$user->id,
            'status' => 'on'
        ];
        $this->data = $data;
      
    }

    public function test_create_notificationUserSetting()
    {
        $data_ = [
            'notification_id'=>$this->notification_id,
            'user_id'=>$this->user_id,
            'status' => 'on'
        ];
      
        $notificationUserSettingRepo = new NotificationUserSettingRepository(new NotificationUserSetting());
        $notificationUserSetting = $notificationUserSettingRepo->create($data_);

        $this->assertInstanceOf(NotificationUserSetting::class, $notificationUserSetting);
        $this->assertEquals($data['status'], $notificationUserSetting->status);
     
    }


    public function test_delete_notificationUserSetting()
    {  
        $data = factory(NotificationUserSetting::class)->create();
        dd($data);
        $notificationUserSettingRepo = new NotificationUserSettingRepository($data);
        $delete = $notificationUserSettingRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_find_auditionsContributors()
    {
        $user = factory(User::class)->create();
        $data = factory(AuditionContributors::class)->create(['auditions_id'=>$this->auditions_id,'user_id'=>$user->id]);
        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $found = $auditionsContributorsRepo->find($data->id);
        $this->assertInstanceOf(AuditionContributors::class,$found);
        $this->assertEquals($found->email,$data->email);

    }

    public function test_all_auditionsContributors()
    {
        $user = factory(User::class)->create();
        factory(AuditionContributors::class,5)->create(['auditions_id'=>$this->auditions_id,'user_id'=>$user->id]);
        $auditionsContributors = new AuditionContributorsRepository(new AuditionContributors());
        $data = $auditionsContributors->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_auditionsContributors_exception()
    {
        $this->expectException(CreateException::class);
        $userRepo = new AuditionContributorsRepository(new AuditionContributors());
        $userRepo->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $auditionsContributors = new AuditionContributorsRepository(new AuditionContributors());
        $auditionsContributors->find(2345);
    }



    public function test_delete_auditionsContributors_null_exception()
    {

        $auditionsContributorsRepo = new AuditionContributorsRepository(new AuditionContributors());
        $delete = $auditionsContributorsRepo->delete();
        $this->assertNull($delete);
    }
}
