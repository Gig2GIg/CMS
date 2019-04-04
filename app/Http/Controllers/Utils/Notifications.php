<?php
namespace App\Http\Controllers\Utils;


class Notifications
{
    const AUTIDION_UPDATE         = 'autidion_update';
    const REPRESENTATION_EMAIL    = 'representation_email';
    const DOCUMENT_UPLOAD         = 'document_upload';
    const CHECK_IN                = 'check_in';
    const AUTIDION_REQUEST        = 'autidion_request';
    const CUSTOM                  = 'custom';

    public static function send($object, $type , $user = null, $data = null, $message = null)
    {
        
        switch ($type) {
            case self::AUTIDION_UPDATE:
                $title = 'Audition Update';
                $message = 'A new update has been added'. $object->title;
                break;
            case self::REPRESENTATION_EMAIL:
                $title = 'Representation Email';
                $message = "Some message";
                break;
            case self::DOCUMENT_UPLOAD:
                $title = 'Document Upload';
                $message = "Some message";
                break;
            case self::CHECK_IN:
                $title = 'Check-in ';
                $message = "Some message";
                break;
            case self::CUSTOM:
                $title =  $object;
                $message = "Some message";
                break;
            default:
        }

        $notification = $audtion->notifications->first();

        if ($audtion->contributors){
            foreach ($audtion->contributors as $contributor) {
                $this->notification_history->create([
                    'title' => $notification->title,
                    'code' => $notification->code,
                    'status' => 'unread'
                ]);

                fcm()
                    ->to([$contributor->pushkey])
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

        if ($audtion->contributors){
            foreach ($audtion->contributors as $contributor) {
                $this->notification_history->create([
                    'title' => $notification->title,
                    'code' => $notification->code,
                    'status' => 'unread'
                ]);

                fcm()
                    ->to([$contributor->pushkey])
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
     
    }
}


// SendNotifications::send(
//     Notification::ORDER_RECEIVED_NOTIFICATION,
//     $rental->client->user,
//     $rental->product
// );
