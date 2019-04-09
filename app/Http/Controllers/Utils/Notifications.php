<?php
namespace App\Http\Controllers\Utils;
use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\UserRepository;
use App\Models\User;


class Notifications
{
    const AUTIDION_UPDATE           = 'autidion_update';
    const AUTIDION_ADD_CONTRIBUIDOR = 'autidion_add_contribuidor';
    const UPCOMING_AUDITION         = 'upcoming_audition'; 
    const REPRESENTATION_EMAIL      = 'representation_email';
    const DOCUMENT_UPLOAD           = 'document_upload';
    const CHECK_IN                  = 'check_in';
    const AUTIDION_REQUEST          = 'autidion_request';
    const CUSTOM                    = 'custom';

    public static function send($audition = null, $type , $user = null, $data = null, $message = null)
    {
        $log = new LogManger();
        switch ($type) {
            case self::AUTIDION_ADD_CONTRIBUIDOR:
                $title = 'Audition Save';
                $message = 'you have been add to  audition '. $audition->title;
               // SEND NOTIFICATION PUSH ALL CONTRIBUIDORS
                foreach ($audition->contributors as $contributor) {
                    $user_repo = new UserRepository(new User);   
                    $user = $user_repo->find($contributor->user_id);
                    $user->notification_history()->create([
                        'title' => $title,
                        'code' => $type,
                        'status' => 'unread',
                        'message'=> $message
                    ]);
                    fcm()
                        ->to([$user->pushkey])
                        ->notification([
                            'title' => $title,
                            'body'  => $message,
                        ])
                        ->send();
                }
                break;
            case self::UPCOMING_AUDITION:
                $log->info("UPCOMMING " . $audition->title);
                $title = 'Audition Upcomming';
                $message = ' you have been upcoming to audition '. $audition->title;
                // SEND NOTIFICATION PUSH TO USER_AUDITION
                foreach ($user->notification_settings_on as $notification_setting) {  
                  
                    if ($notification_setting->code == $type && $notification_setting->status == 'on' )
                        $user->notification_history()->create([
                            'title' => $title,
                            'code' => $type,
                            'status' => 'unread',
                            'message'=> $message
                        ]);
                        
                        fcm()
                            ->to([$user->pushkey])
                            ->notification([
                                'title' => $title,
                                'body'  => $message,
                            ])
                            ->send();
                } 
                break;  
            case self::AUTIDION_UPDATE:
                $title = 'Audition Update';
                $message = 'A new update has been added '. $audition->title;
                // SEND NOTIFICATION PUSH ALL CONTRIBUIDORS
                foreach ($audition->contributors as $contributor) {
                    $user_repo = new UserRepository(new User);   
                    $user = $user_repo->find($contributor->user_id);
                    $user->notification_history()->create([
                        'title' => $title,
                        'code' => $type,
                        'status' => 'unread',
                        'message'=> $message
                    ]);
                    fcm()
                        ->to([$contributor->pushkey])
                        ->notification([
                            'title' => $title,
                            'body'  => $message,
                        ])
                        ->send();
                }
                // SEND NOTIFICATION PUSH TO USER_AUDITION
                foreach ($audition->userauditions as $userauditions) {
                    $user_repo = new UserRepository(new User);
                    $user = $user_repo->find($userauditions->user_id);
                    foreach ($user->notification_settings_on as $notification_setting) {  
                        if ($notification_setting->code == $type && $notification_setting->status == 'on' )
                            $user->notification_history()->create([
                                'title' => $title,
                                'code' => $type,
                                'status' => 'unread',
                                'message'=> $message
                            ]);
                            
                            fcm()
                                ->to([$user->pushkey])
                                ->notification([
                                    'title' => $title,
                                    'body'  => $message,
                                ])
                                ->send();
                    } 
                }
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
                $title =  $audition;
                $message = "Some message";
                break;
            default:
        }    
    }
}

//EXAMPLE TO SEND NOTIFICATION
// SendNotifications::send(
//     $audition,
//     $type
// );