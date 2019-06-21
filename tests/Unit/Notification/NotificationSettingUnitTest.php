<?php

namespace Tests\Unit\Notification;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\Notifications\Notification;
use App\Models\Notifications\NotificationSetting;
use App\Http\Repositories\Notification\NotificationRepository;
use App\Http\Repositories\Notification\NotificationSettingRepository;

class NotificationSettingUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_all_notification_setting(){

        $data = [
            'status' => 'on',
            'code' => 'autidion_update',
        ];

        $data2 = [
            'status' => 'on',
            'code' => 'upcoming_audition',
        ];

        factory(NotificationSetting::class)->create( $data);
        factory(NotificationSetting::class)->create( $data2);
        $dataAll = new NotificationSettingRepository(new NotificationSetting());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 1);
    }

    public function test_create_notification_setting()
    {
        $notificationable_type = 'auditions';
        $data = [
            'status' => 'on',
            'code' => 'autidion_update',
        ];


        $notification_repo = new NotificationSettingRepository(new NotificationSetting());
        $notification_setting = $notification_repo->create($data);

        $this->assertInstanceOf(NotificationSetting::class, $notification_setting);
        $this->assertEquals($data['code'], $notification_setting->code);
        $this->assertEquals($data['status'], $notification_setting->status);
    }

    public function test_show_notification_setting()
    {
        $notification_setting = factory(NotificationSetting::class)->create();
        $notification_setting_repo = new NotificationSettingRepository(new NotificationSetting());
        $found =  $notification_setting_repo->find($notification_setting->id);

        $this->assertInstanceOf(NotificationSetting::class, $found);
        $this->assertEquals($found->code,$notification_setting->code);
        $this->assertEquals($found->label,$notification_setting->label);

    }

    public function test_update_notification_setting()
    {
        $data = [
            'status' => 'off'
        ];

        $notification_setting = factory(NotificationSetting::class)->create();

        $notification_setting_repo = new NotificationSettingRepository($notification_setting);
        $update = $notification_setting_repo->update($data);
        $this->assertTrue($update);
        $this->assertInstanceOf(NotificationSetting::class, $notification_setting);
        $this->assertEquals($data['status'], $notification_setting->status);
    }


    public function test_delete_notification_setting()
    {
        $notification_setting = factory(NotificationSetting::class)->create();;

        $notification_setting_repo = new NotificationSettingRepository($notification_setting);
        $delete = $notification_setting_repo->delete();
        $this->assertTrue($delete);
    }


    public function test_create_notification_setting_exception()
    {
        $this->expectException(CreateException::class);
        $notification_setting_repo = new NotificationSettingRepository(new NotificationSetting());
        $notification_setting_repo->create([]);
    }

    public function test_show_notification_setting_place_exception()
    {
        $this->expectException(NotFoundException::class);

        $notification_setting = factory(NotificationSetting::class)->create();

        $notification_setting_repo = new NotificationSettingRepository($notification_setting);

        $notification_setting_repo->find(282374);
    }

    public function test_update_notification_setting_exception()
    {
        $this->expectException(UpdateException::class);
        $notification_setting = factory(NotificationSetting::class)->create();

        $notification_setting_repo =  new NotificationSettingRepository($notification_setting);
        $data = ['status'=>null];
        $notification_setting_repo->update($data);
    }

    public function test_notification_setting_delete_null()
    {
        $notification_setting_repo = new NotificationSettingRepository(new NotificationSetting());
        $delete = $notification_setting_repo->delete();
        $this->assertNull($delete);

    }
}
