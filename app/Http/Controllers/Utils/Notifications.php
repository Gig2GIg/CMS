<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-06
 * Time: 13:59
 */

namespace App\Http\Controllers\Utils;




class Notifications
{
    public static function send($type, $user, $data = null, $message = null)
    {
        $notification = $user->notification_history()->create([
            'title'    => $message,
            'code'    => $type,
            'status' => $user->id,
        ]);

        fcm()
            ->to([$user->device_token])
            ->data([
                'notification_id' => $notification->id,
            ])
            ->notification([
                'title' => $title,
                'body'  => $message,
            ])
            ->send();
    }
}


// Notification::send(
//     Notification::ORDER_RECEIVED_NOTIFICATION,
//     $rental->client->user,
//     $rental->product
// );