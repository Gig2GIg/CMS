<?php

namespace Tests\Unit\Notification;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Models\Notifications\Notification;
use App\Http\Repositories\Notification\NotificationRepository;

class NotificationUnitTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_all_notification_custom(){
        $type = 'custom';
        $notificationable_type = 'auditions';  

        $data = [
            'type' => $type,
            'notificationable_type' => $notificationable_type
        ];
        factory(Notification::class,5)->create( $data);

        $dataAll = new NotificationRepository(new Notification());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 

    public function test_all_notification_audition(){
        $type = 'audition';
        $notificationable_type = 'auditions';  

        $data = ['type' => $type, 'notificationable_type' => $notificationable_type];
        factory(Notification::class,5)->create( $data);

        $dataAll = new NotificationRepository(new Notification());
        $dataTest = $dataAll->all();
        $this->assertIsArray($dataTest->toArray());
        $this->assertTrue($dataTest->count() > 2);
    } 


    public function test_create_notification_type_app()
    {
    
        $notificationable_type = 'auditions';  
        $data = [
            'title' => $this->faker->name,
            'code' => 'XOWEWEW',
            'type' => 'audition',
            'notificationable_type' =>  $notificationable_type,
            'notificationable_id' => $this->faker->numberBetween(1,2)
        ];

        $notification_repo = new NotificationRepository(new Notification());
        $notification = $notification_repo->create($data);
     
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($data['type'], $notification->type);

    }

    public function test_create_notification_type_custom()
    {
        $notificationable_type = 'custom';  
        $data = [
            'title' => $this->faker->name,
            'code' => 'XOWEWEW',
            'type' => 'custom',
            'notificationable_type' =>  $notificationable_type,
            'notificationable_id' => $this->faker->numberBetween(1,2)
        ];

        $notification_repo = new NotificationRepository(new Notification());
        $notification = $notification_repo->create($data);
     
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($data['type'], $notification->type);

    }

    public function test_create_notification_type_audition()
    {
        $notificationable_type = 'auditions';  
        $data = [
            'title' => $this->faker->name,
            'code' => 'XOWEWEW',
            'type' => 'audition',
            'notificationable_type' =>  $notificationable_type,
            'notificationable_id' => $this->faker->numberBetween(1,2)
        ];


        $notification_repo = new NotificationRepository(new Notification());
        $notification = $notification_repo->create($data);
     
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);
        $this->assertEquals($data['type'], $notification->type);

    }

    public function test_show_notification()
    {
        $type = 'custom';
        $notificationable_type = 'auditions';  

        $data = ['type' => $type, 'notificationable_type' => $notificationable_type];
        $notification = factory(Notification::class)->create( $data);

        $notification_repo = new NotificationRepository(new Notification());
        $found =  $notification_repo->find($notification->id);

        $this->assertInstanceOf(Notification::class, $found);
        $this->assertEquals($found->code,$notification->code);
        $this->assertEquals($found->title,$notification->title);
    }

    public function test_update_notification()
    {
        $notificationable_type = 'auditions';  
        $data_old = [
            'title' => $this->faker->name,
            'code' => 'XOWEWEW',
            'type' => 'audition',
            'notificationable_type' =>  $notificationable_type,
            'notificationable_id' => $this->faker->numberBetween(1,2)
        ];

        $notification = factory(Notification::class)->create($data_old);

        $data = [
            'title' => $this->faker->name,
            'code' => $this->faker->text(),
            'description' => $this->faker->paragraph()
        ];

        $notification_repo = new NotificationRepository($notification);
        $update = $notification_repo->update($data);
        $this->assertTrue($update);
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($data['code'], $notification->code);
        $this->assertEquals($data['title'], $notification->title);    
    }


    public function test_delete_notification()
    {
        $notificationable_type = 'auditions';  
        $data = [
            'title' => $this->faker->name,
            'code' => 'XOWEWEW',
            'type' => 'audition',
            'notificationable_type' =>  $notificationable_type,
            'notificationable_id' => $this->faker->numberBetween(1,2)
        ];

        $notification = factory(Notification::class)->create($data);

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
        $notificationable_type = 'auditions';  
        $data_old = [
            'title' => $this->faker->name,
            'code' => 'XOWEWEW',
            'type' => 'audition',
            'notificationable_type' =>  $notificationable_type,
            'notificationable_id' => $this->faker->numberBetween(1,2)
        ];

        $notification = factory(Notification::class)->create($data_old);

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
