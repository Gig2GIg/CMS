<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\ContentSetting;
use App\Http\Repositories\ContentSettingRepository;

class ContentSettingUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;


    public function test_show_content_setting()
    {
        $contentSetting = factory(ContentSetting::class)->create();
        $contentSettingRepo = new ContentSettingRepository(new ContentSetting());
        $found =  $contentSettingRepo->find($contentSetting->id);

        $this->assertInstanceOf(ContentSetting::class, $found);
        $this->assertEquals($found->term_of_use,$contentSetting->term_of_use);
        $this->assertEquals($found->privacy_policy,$contentSetting->privacy_policy);
        $this->assertEquals($found->app_info,$contentSetting->app_info);
        $this->assertEquals($found->contact_us,$contentSetting->contact_us);
    }

    public function test_update_content_setting()
    {
        $contentSetting = factory(ContentSetting::class)->create();

        $data = [
            'term_of_use' => 'Dolor et sea lorem clita aliquyam.',
            'privacy_policy' => 'Dolor et sea lorem clita ',
            'app_info' => 'At sit et sit dolores, aliquyam',
            'contact_us' => 'nvidunt consetetur sit accusam et lorem, diam aliquyam'
        ];

        $contentSettingRepo = new ContentSettingRepository($contentSetting);
        $update = $contentSettingRepo->update($data);
      
        $this->assertTrue($update);
        $this->assertEquals($data['term_of_use'], $contentSetting->term_of_use);
        $this->assertEquals($data['privacy_policy'], $contentSetting->privacy_policy);
        $this->assertEquals($data['app_info'], $contentSetting->app_info);
        $this->assertEquals($data['contact_us'], $contentSetting->contact_us);
    }


    public function test_delete_content_setting()
    {
        $contentSetting = factory(ContentSetting::class)->create();
        $contentSettingRepo = new ContentSettingRepository($contentSetting);
        $delete = $contentSettingRepo->delete();
        $this->assertTrue($delete);
    }


    public function test_create_content_setting_exception()
    {
        $this->expectException(CreateException::class);
        $contentSettingRepo = new ContentSettingRepository(new ContentSetting());
        $contentSettingRepo->create([]);
    }

    public function test_show_content_setting_exception()
    {
        $this->expectException(NotFoundException::class);
        $contentSettingRepo = new ContentSettingRepository(new ContentSetting());
        $contentSettingRepo->find(282374);
    }

    public function test_update_content_setting_exception()
    {
        $this->expectException(UpdateException::class);
        $contentSetting = factory(ContentSetting::class)->create();
        $contentSettingRepo = new ContentSettingRepository($contentSetting);
        $data = ['privacy_policy'=>null];
        $contentSettingRepo->update($data);
    }

    public function test_content_setting_delete_null()
    {
        $contentSettingRepo = new ContentSettingRepository(new ContentSetting());
        $delete = $contentSettingRepo->delete();
        $this->assertNull($delete);

    }

}
