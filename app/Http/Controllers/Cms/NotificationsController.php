<?php


namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Utils\Notifications as SendNotifications;
use App\Http\Controllers\Controller;
use App\Http\Repositories\TypeProductsRepository;
use App\Http\Repositories\Notification\NotificationRepository;
use App\Models\Notifications\Notification;
use App\Http\Exceptions\NotFoundException;
use Illuminate\Database\QueryException;
use App\Http\Requests\NotificationsRequest;
use App\Http\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

    public function sendNotifications(NotificationsRequest $request)
    {
        if ($request->json())
        {
           $notification =  $this->createNotification($request->title);

           $this->sendPushNotification(
                null,
                'cms',
                null,
                $request->title,
                $request->message
            );
           $this->log->info($request->title);
            $this->log->info($request->message);

            return response()->json(['data' => 'Notification send'], 200);
        }else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function sendNotificationToUser(Request $request)
    {
        if ($request->json())
        {

            $user = new UserRepository(new User());
            $dataUser = $user->find($request->id);

           $notification = $this->createNotification($request->title);

           $this->sendPushNotification(
                null,
                'cms_to_user',
                $dataUser,
                $request->title
            );

            return response()->json(['data' => 'Notification send'], 200);
        }else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    public function createNotification($title)
    {
        try {
            $notificationData = [
                'title' => $title,
                'code' => Str::random(12),
                'type' => 'custom',
                'notificationable_type' => 'users',
                'notificationable_id' => $this->getUserLogging()
            ];

            if ($title !== null) {
                $notificationRepo = new NotificationRepository(new Notification());
                $notificationRepo->create($notificationData);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }

    }

}
