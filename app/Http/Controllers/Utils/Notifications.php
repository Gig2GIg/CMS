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
    const CMS                       = 'cms';

    public static function send($audition, $type , $user = null, $title = null)
    {
        $log = new LogManger();
        switch ($type) {
            case self::AUTIDION_ADD_CONTRIBUIDOR:
                $log->info("AUDITION SAVE " . $audition->title);
                $title = 'Audition Save';
                $message = 'you have been add to  audition '. $audition->title;
                $to = 'MANY';
                break;
            case self::UPCOMING_AUDITION:
                $log->info("UPCOMMING " . $audition->title);
                $title = 'Audition Upcomming';
                $message = ' you have been upcoming to audition '. $audition->title;
                $to = 'ONE';
                break;  
            case self::AUTIDION_UPDATE:
                $log->info("AUDITION UPDATE " . $audition->title);
                $title = 'Audition Update';
                $message = 'A new update has been added '. $audition->title;    
                $to = 'MANY';
                break;
            case self::REPRESENTATION_EMAIL:
                $log->info("REPRESENTATION EMAIL SEND " . $user->email);
                $title = 'Representation Email';
                $message = "Some message";
                $to = 'ONE';
                break;
            case self::DOCUMENT_UPLOAD:
                $log->info("DOCUMENT_UPLOAD") ;
                $title = 'Document Upload';
                $message = "Some message";
                $to = 'ONE';
                break;
            case self::CHECK_IN:
                $log->info("CHECK_IN " . $audition->title);
                $title = 'Check-in ';
                $message = 'you have been registered for the audition '. $audition->title;    
                $to = 'ONE';
                break;
            case self::CUSTOM:
                $log->info("CUSTOM");
                $title =  $title;
                $message = $title;
                $to = 'MANY';
                break;
            case self::CMS:
         
                $log->info("CMS");
                $title =  $title;
                $message = $title;
                break;
            default:
        }    
        if ($type == 'cms'){
            $user = User::all();
            $user->each(function ($user) use ($title, $type) {
                $user->notification_history()->create([
                    'title' => $title,
                    'code' => $type,
                    'status' => 'unread',
                    'message'=> $title
                ]);
             
                fcm()
                    ->to([$user->pushkey])
                    ->notification([
                        'title' => $title,
                        'body'  => $title,
                    ])
                    ->send();  
            });

        }

        if ($audition !== null || $user !== null ){
            if ($to == 'MANY'){
                if ($type == 'custom') {
                    $audition->userauditions->each(function ($useraudition) use ($title, $message, $type) {
                        $userRepo = new UserRepository(new User);
                        $user_result = $userRepo->find($useraudition->user_id);
                        $user_result->notification_history()->create([
                            'title' => $title,
                            'code' => $type,
                            'status' => 'unread',
                            'message'=> $message
                        ]);
                        
                        fcm()
                            ->to([$user_result->pushkey])
                            ->notification([
                                'title' => $title,
                                'body'  => $message,
                            ])
                            ->send();  
                    });  
                }
                $audition->contributors->each(function ($contributor) use ($title, $message, $type) {
                    $userRepo = new UserRepository(new User);
                    $user_result = $userRepo->find($contributor->user_id);
                    $user_result->notification_history()->create([
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
                });    
              
            }elseif ($to == 'ONE' &&  ($user->notification_settings_on->count() > 0)  ){  
                $user->notification_settings_on->each(function ($notification) use ($title, $message, $type, $user) {
                    if ($notification->code == $type && $notification->status == 'on')
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
                });
            }
        }

    }
}

//EXAMPLE TO SEND NOTIFICATION MANY
// use App\Http\Controllers\Utils\Notifications as SendNotifications;
// SendNotifications::send(
//     $audition,
//     $type
// );
//EXAMPLE TO SEND NOTIFICATION ONE
// use App\Http\Controllers\Utils\Notifications as SendNotifications;
// SendNotifications::send(
//     $audition,
//     $type,
//     $user
// );
//EXAMPLE TO SEND NOTIFICATION ONE CUSTOM
// use App\Http\Controllers\Utils\Notifications as SendNotifications;
// SendNotifications::send(
//     $audition,
//     $type,
//     $user,
//     $title
// );