<?php
namespace App\Http\Controllers\Utils;
use App\Http\Repositories\UserRepository;
use App\Models\User;


class Notifications
{
    const AUTIDION_UPDATE           = 'autidion_update';
    const AUTIDION_ADD_CONTRIBUIDOR = 'autidion_add_contribuidor';
    const REPRESENTATION_EMAIL      = 'representation_email';
    const DOCUMENT_UPLOAD           = 'document_upload';
    const CHECK_IN                  = 'check_in';
    const AUTIDION_REQUEST          = 'autidion_request';
    const CUSTOM                    = 'custom';

    public static function send($object, $type , $user = null, $data = null, $message = null)
    {
        
        switch ($type) {
            case self::AUTIDION_ADD_CONTRIBUIDOR:
                $title = 'Audition Created';
                $message = 'you have been added to the audition'. $object->title;
                break;
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
        $notification = $object->notifications->first();

        if (!! $object->contributors){
            foreach ($object->contributors as $contributor) {
                $user_repo = new UserRepository(new User);   
                $user = $user_repo->find($contributor->user_id);
                $user->notification_history()->create([
                    'title' => $notification->title,
                    'code' => $notification->code,
                    'status' => 'unread',
                    'message'=> $title
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

//EXAMPLE TO SEND NOTIFICATION
// SendNotifications::send(
//     $auditions,
//     $type
// );