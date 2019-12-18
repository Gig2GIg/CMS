<?php

namespace Tests\Unit;

use App\Http\Repositories\UserSettingsRepository;
use App\Models\User;
use App\Models\UserSettings;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserSettingsUnitTest extends TestCase
{
    public function test_save_configs_users()
    {
        $user = factory(User::class)->create();
        $repo = new UserSettingsRepository(new UserSettings());
        $data = [
            'user_id' => $user->id,
            'setting' => 'FEEDBACK',
            'value' => true,
        ];

        $setting = $repo->create($data);

        $this->assertInstanceOf(UserSettings::class, $setting);
    }

    public function test_get_by_user()
    {
        $user = factory(User::class)->create();
        factory(UserSettings::class, 10)->create(['user_id' => $user->id]);

        $repo = new UserSettingsRepository(new UserSettings());
        $data = $repo->findbyparam('user_id', $user->id)->get();

        $this->assertTrue($data->count() > 0);

    }

    public function test_get_setting_user_by_id()
    {
        $user = factory(User::class)->create();
        $setting = factory(UserSettings::class)->create([
            'user_id' => $user->id,
        ]);

        $repo = new UserSettingsRepository(new UserSettings());
        $data = $repo->find($setting->id);
        $this->assertEquals($setting->user_id, $data->user_id);
        $this->assertEquals($setting->setting, $data->setting);
        $this->assertEquals($setting->value, $data->value);
    }

    public function test_update_settings_user(){
        $user = factory(User::class)->create();
        $setting = factory(UserSettings::class)->create([
            'user_id' => $user->id,
            'value'=>false
        ]);

        $data = [
          'value'=>true
        ];

        $repo = new UserSettingsRepository($setting);

        $update = $repo->update($data);
        $this->assertTrue($update);

    }
}
