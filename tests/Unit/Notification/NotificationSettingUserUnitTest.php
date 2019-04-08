<?php

namespace Tests\Unit\Notification;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\Notifications\NotificationSettingUser;
use App\Models\Notifications\NotificationSetting;
use App\Models\User;
use App\Http\Repositories\Notification\NotificationSettingUserRepository;

class NotificationSettingUserUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $user_id;
    protected $notification_setting_id;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $this->user_id = $user->id;
    
        $notification_setting= factory(NotificationSetting::class)->create();
        $this->notification_setting_id = $notification_setting->id;

    }

    public function test_create_notificationSettingUser()
    {
        $data = [
            'user_id' => $this->user_id,
            'notification_setting_id' => $this->notification_setting_id,
            'status' => 'on'
        ];

        $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
        $notificationSettingUser = $notificationSettingUserRepo->create($data);
        $this->assertInstanceOf(NotificationSettingUser::class, $notificationSettingUser);
        $this->assertEquals($data['status'], $notificationSettingUser->status);
     
    }


    public function test_change_statusnotificationSettingUser()
    {  
        $data = [
            'notification_setting_id'=> $this->notification_setting_id,
            'user_id'=>$this->user_id,
            'status' => 'on'
        ];
      
        $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
        $notificationUserSetting = $notificationSettingUserRepo->create($data);
        $notificationUserSetting->update(['status' => 2]);    
        $this->assertEquals(2, $notificationUserSetting->status);
    }

    public function test_find_notificationSettingUser()
    {
        $data = [
            'notification_setting_id'=>$this->notification_setting_id,
            'user_id'=>$this->user_id,
            'status' => 'on'
        ];
      
        $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
        $notificationSettingUser = $notificationSettingUserRepo->create($data);
        $found = $notificationSettingUserRepo->find($notificationSettingUser->id);
        $this->assertInstanceOf(NotificationSettingUser::class,$found);
        $this->assertEquals($found->id,$notificationSettingUser->id);
    }

    public function test_allnotificationSettingUser()
    {

        $data = [
            'notification_setting_id'=>$this->notification_setting_id,
            'user_id'=>$this->user_id,
            'status' => 'on'
        ];
      
        factory(NotificationSettingUser::class, 5)->create($data);
        $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
        $data = $notificationSettingUserRepo->all();
        $this->assertIsArray($data->toArray());
        $this->assertTrue($data->count() > 2);
    }

    public function test_create_notificationSettingUser_exception()
    {
        $this->expectException(CreateException::class);
        $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
        $notificationSettingUserRepo->create([]);
    }

    public function test_show_notificationSettingUser_exception()
    {
        $this->expectException(NotFoundException::class);
        $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
        $notificationSettingUserRepo->find(2345);
    }



    public function test_delete_notificationSettingUser_null_exception()
    {
        $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
        $delete = $notificationSettingUserRepo->delete();
        $this->assertNull($delete);
    }
}
