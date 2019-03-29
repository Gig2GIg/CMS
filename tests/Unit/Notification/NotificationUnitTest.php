<?php

namespace Tests\Unit\Notification;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\Notification;
use App\Http\Repositories\Notification\NotificationRepository;

class NotificationUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_all_notification_app(){
        $type = 'app';
        factory(Notification::class,5)->create(['type' => $type]);
        $dataAll = new NotificationRepository(new Notification());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function test_all_notification_custom(){
        $type = 'custom';
        factory(Notification::class,5)->create(['type' => $type]);
        $dataAll = new NotificationRepository(new Notification());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function test_all_notification_audition(){
        $type = 'audition';
        factory(Notification::class,5)->create(['type' => $type]);
        $dataAll = new NotificationRepository(new Notification());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 


    public function test_create_notification_type_app()
    {
        $data = [
            'title' => $this->faker->name,
            'code' => $this->faker->text(),
            'description' => $this->faker->paragraph(),
            'type' => 'app'
        ];

        $notification_repo = new NotificationRepository(new Notification());
        $notification = $notification_repo->create($data);
     
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($data['description'], $notification->description);
        $this->assertEquals($data['type'], $notification->type);

    }

    public function test_create_notification_type_custom()
    {
        $data = [
            'title' => $this->faker->name,
            'code' => $this->faker->text(),
            'description' => $this->faker->paragraph(),
            'type' => 'custom'
        ];

        $notification_repo = new NotificationRepository(new Notification());
        $notification = $notification_repo->create($data);
     
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($data['description'], $notification->description);
        $this->assertEquals($data['type'], $notification->type);

    }

    public function test_create_notification_type_audition()
    {
        $data = [
            'title' => $this->faker->name,
            'code' => $this->faker->text(),
            'description' => $this->faker->paragraph(),
            'type' => 'audition'
        ];

        $notification_repo = new NotificationRepository(new Notification());
        $notification = $notification_repo->create($data);
     
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($data['description'], $notification->description);
        $this->assertEquals($data['type'], $notification->type);

    }

    public function test_show_notification()
    {
        $type = 'app';
        $notification = factory(Notification::class)->create(['type' => $type]);
        $notification_repo = new NotificationRepository(new Notification());
        $found =  $notification_repo->find($notification->id);

        $this->assertInstanceOf(Notification::class, $found);
        $this->assertEquals($found->code,$notification->code);
        $this->assertEquals($found->title,$notification->title);
    }

    public function test_update_notification()
    {
        $type = 'app';
        $notification = factory(Notification::class)->create(['type' => $type]);

        $data = [
            'title' => $this->faker->name,
            'code' => $this->faker->text(),
            'description' => $this->faker->paragraph(),
            'type' => 'custom'
        ];

        $notification_repo = new NotificationRepository($notification);
        $update = $notification_repo->update($data);
        $this->assertTrue($update);
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($data['description'], $notification->description);
        $this->assertEquals($data['type'], $notification->type);
    }


    public function test_delete_notification()
    {
        $type = 'app';
        $notification = factory(Notification::class)->create(['type' => $type]);
        $notification_repo = new NotificationRepository($notification);
        $delete = $notification_repo->delete();
        $this->assertTrue($delete);
    }


    public function test_create_notification_exception()
    {
        $this->expectException(CreateException::class);
        $notification_repo = new NotificationRepository(new Notification());
        $notification_repo->create([]);
    }

    public function test_show_notification_place_exception()
    {
        $this->expectException(NotFoundException::class);
        $notification_repo = new NotificationRepository(new Notification());
        $notification_repo->find(282374);
    }

    public function test_update_notification_place_exception()
    {
        $this->expectException(UpdateException::class);
        $type = 'app';
        $notification = factory(Notification::class)->create(['type' => $type]);
        $notification_repo =  new NotificationRepository($notification);
        $data = ['title'=>null];
        $notification_repo->update($data);
    }

    public function test_notification_place_delete_null()
    {
        $notification_repo = new NotificationRepository(new Notification());
        $delete = $notification_repo->delete();
        $this->assertNull($delete);

    }
}
