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

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $this->user_id = $user->id;
        $type = 'app';
        $notification = factory(Notification::class)->create(['type' => $type]);
        $this->notification_id = $notification->id;

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

        $type = 'app';
        $notification = factory(Notification::class)->create(['type' => $type]);

        $this->assertInstanceOf(NotificationUserSetting::class, $notificationUserSetting);
        $this->assertEquals($data_['status'], $notificationUserSetting->status);
     
    }


    public function test_change_status_notificationUserSetting()
    {  
        $data = [
            'notification_id'=>$this->notification_id,
            'user_id'=>$this->user_id,
            'status' => 'on'
        ];
      
        $notification_user_settingRepo = new NotificationUserSettingRepository(new NotificationUserSetting());
        $notificationUserSetting = $notification_user_settingRepo->create($data);
        $notificationUserSetting->update(['status' => 2]);
        
        $this->assertEquals(2, $notificationUserSetting->status);
    
    }

    public function test_find_notificationUserSetting()
    {
        $data = [
            'notification_id'=>$this->notification_id,
            'user_id'=>$this->user_id,
            'status' => 'on'
        ];
      
        $notification_user_settingRepo = new NotificationUserSettingRepository(new NotificationUserSetting());
        $notificationUserSetting = $notification_user_settingRepo->create($data);

        $found = $notification_user_settingRepo->find($notificationUserSetting->id);
        $this->assertInstanceOf(NotificationUserSetting::class,$found);
        $this->assertEquals($found->id,$notificationUserSetting->id);

    }

    public function test_all_NotificationUserSetting()
    {

        factory(NotificationUserSetting::class, 5)->create(['notification_id'=>$this->notification_id, 'user_id'=>$this->user_id]);
        $notificationUserSetting = new NotificationUserSettingRepository(new NotificationUserSetting());
        $data = $notificationUserSetting->all();

        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_NotificationUserSetting_exception()
    {
        $this->expectException(CreateException::class);
        $notificationUserSetting = new NotificationUserSettingRepository(new NotificationUserSetting());
        $notificationUserSetting->create([]);
    }

    public function test_show_user_exception()
    {
        $this->expectException(NotFoundException::class);
        $notificationUserSetting = new NotificationUserSettingRepository(new NotificationUserSetting());
        $notificationUserSetting->find(2345);
    }



    public function test_delete_auditionsContributors_null_exception()
    {
        $notificationUserSetting = new NotificationUserSettingRepository(new NotificationUserSetting());
        $delete = $notificationUserSetting->delete();
        $this->assertNull($delete);
    }
}
