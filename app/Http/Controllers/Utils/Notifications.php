<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotificationException;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use App\Models\UserAuditions;
use Illuminate\Support\Collection;

class Notifications
{
    const AUTIDION_UPDATE = 'autidion_update';
    const AUTIDION_ADD_CONTRIBUIDOR = 'autidion_add_contribuidor';
    const AUTIDION_ADD_IN_UPDATE_CONTRIBUIDOR = 'autidion_add_in_update_contribuidor';
    const UPCOMING_AUDITION = 'upcoming_audition';
    const REPRESENTATION_EMAIL = 'representation_email';
    const DOCUMENT_UPLOAD = 'document_upload';
    const CHECK_IN = 'check_in';
    const AUTIDION_REQUEST = 'autidion_request';
    const CUSTOM = 'custom';
    const CMS = 'cms';
    const CMS_TO_USER = 'cms_to_user';
    const ICON = '/images/logo-color-push.png';
    const APPOINTMENT_REORDER = 'appointment_reorder';
    const INSTANT_FEEDBACK = 'instant_feedback';
    const FEEDBACK = 'feedback';
    const CASTER_AUDITION_INVITE = 'caster_audition_invite';
    const AUDITION_CREATED = 'audition_created';
    const NEW_ONLINE_MEDIA = 'new_online_media';
    const PERFORMER_SELECTED = 'performer_selected';
    const CASTER_TO_PERFORMER = 'caster_to_performer';

    public static function send($audition = null, $type, $user = null, $title = null, $message = null, $clickToSend = "")
    {
        try {

            $log = new LogManger();
            switch ($type) {
                case self::AUTIDION_ADD_CONTRIBUIDOR:
                    $log->info("PUSH NOTIFICATION AUDITION SAVE / CONTRIBUTOR ADD " . $audition->title);
                    $title = 'Audition Save';
                    // $message = 'You have been added to the audition ' . $audition->title;
                    $message = 'Contributor invitation available for ' . $audition->title;
                    $to = 'MANY';
                    $clickToSend = env('CASTER_URL');
                    break;
                case self::AUTIDION_ADD_IN_UPDATE_CONTRIBUIDOR:
                    $log->info("PUSH NOTIFICATION AUDITION CONTRIBUTOR ADD " . $audition->title);
                    $title = 'Audition Update';
                    $message = 'Contributor invitation available for ' . $audition->title;
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('CASTER_URL');
                    break;
                case self::UPCOMING_AUDITION:
                    $log->info("PUSH NOTIFICATION UPCOMMING " . $audition->title);
                    $title = $audition->title;
                    $message = 'You have been added to upcoming audition ' . $audition->title;
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::AUTIDION_UPDATE:
                    $log->info("PUSH NOTIFICATION AUDITION UPDATE " . $audition->title);
                    $title = 'Audition Update';
                    $message = 'A new update has been added ' . $audition->title;
                    $to = 'MANY';
                    $clickToSend = env('CASTER_URL');
                    break;
                case self::REPRESENTATION_EMAIL:
                    $log->info("PUSH NOTIFICATION REPRESENTATION EMAIL SEND " . $user->email);
                    $title = 'Representation Email';
                    $message = "Some message";
                    $to = 'ONE';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::CASTER_TO_PERFORMER:
                    $log->info("PUSH NOTIFICATION CASTER TO PERFORMER PRIOR TO AUDITION " . $audition->title);
                    $to = 'MANY';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::DOCUMENT_UPLOAD:
                    $log->info("PUSH NOTIFICATION DOCUMENT_UPLOAD");
                    $title = 'Document Upload';
                    $message = "Some message";
                    $to = 'ONE';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::CHECK_IN:
                    $log->info("PUSH NOTIFICATION  CHECK_IN " . $audition->title);
                    $title = $audition->title;
                    $message = 'You have been registered for the audition ' . $audition->title;
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::CUSTOM:
                    $log->info("PUSH NOTIFICATION  CUSTOM");
                    $to = 'MANY';
                    $clickToSend = env('CASTER_URL');
                    break;
                case self::CMS:
                    $log->info("PUSH NOTIFICATION FROM CMS");
                    $to = 'NONE';
                    $clickToSend = '';
                    break;
                case self::CMS_TO_USER:
                    $log->info("PUSH NOTIFICATION FROM CMS TO USER");
                    $to = 'ONE';
                    $clickToSend = '';
                    break;
                case self::INSTANT_FEEDBACK:
                    $log->info("PUSH NOTIFICATION OF INSTANT_FEEDBACK FROM CASTER TO PERFORMER FOR AUDITION" . $audition->title);
                    $appointment_id = $title;
                    $title = $audition->title;
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('PERFORMER_BASE_URL') . '/my/auditions?tab=upcoming&appointment_id=' . $appointment_id . '&performer_id=' . $user->id;
                    break;
                case self::FEEDBACK:
                    $log->info("PUSH NOTIFICATION OF FEEDBACK FROM CASTER TO PERFORMER FOR AUDITION" . $audition->title);
                    $appointment_id = $title;
                    $title = $audition->title;
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::PERFORMER_SELECTED:
                    $log->info("PUSH NOTIFICATION OF PERFORMER_SELECTED FROM CASTER TO PERFORMER FOR AUDITION" . $audition->title);
                    $title = $audition->title;
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::APPOINTMENT_REORDER:
                    $log->info("PUSH NOTIFICATION FROM CASTER TO PERFORMER ABOUT APPOINTMENT TIME UPDATE FOR THE AUDITION" . $audition->title);
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::CASTER_AUDITION_INVITE:
                    $log->info("PUSH NOTIFICATION FROM CMS TO CASTER ABOUT INVITE IN THE AUDITION" . $audition->title);
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('CASTER_URL');
                    break;
                case self::AUDITION_CREATED:
                    $log->info("PUSH NOTIFICATION FROM CMS TO DIRECTOR ABOUT AUDITION CREATED WITH TITLE" . $audition->title);
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('CASTER_URL');
                    break;
                case self::NEW_ONLINE_MEDIA:
                    $log->info("PUSH NOTIFICATION FROM CMS TO DIRECTOR ABOUT NEW ONLINE MEDIA SUBMITTED IN AUDITION CREATED WITH TITLE" . $audition->title);
                    $to = 'ONLY_ONE_WITHOUT_CHECK';
                    $clickToSend = env('CASTER_URL');
                    break;
                default:
            }

            if ($type == 'cms' || $type == 'cms_to_user') {
                if ($to == 'ONE') {
                    if($user->details && (($user->details->type == 2 && $user->is_premium == 1) || $user->details->type != 2)){
                        $user->notification_history()->create([
                            'title' => $title,
                            'code' => $type,
                            'status' => 'unread',
                            'message' => $title,
                        ]);
                        
                        $tokenArray = new Collection();
                        $webTokenArray = new Collection();
                        $user->pushkey->each(function ($user_token_detail) use ($tokenArray, $webTokenArray) {
                            if($user_token_detail->device_token){
                                if($user_token_detail->device_type == 'web'){
                                    $webTokenArray->push($user_token_detail->device_token);
                                }else{
                                    $tokenArray->push($user_token_detail->device_token);
                                }
                            }
                        });

                        $tokens = $tokenArray->unique()->values()->toArray();
                        $webTokens = $webTokenArray->unique()->values()->toArray();
                        
                        $notification = array();
                        $notification['title'] = $title;
                        $notification['body'] = $message;
                        $notification['icon'] = self::ICON;
                        $notification['click_action'] = '';
                        
                        $fcm = fcm();

                        $fcm->to($tokens);
                        $fcm->notification($notification);
                        $fcm->send();

                        //send to web with click action
                        if(count($webTokens) != 0){
                            $notification['click_action'] = $clickToSend;

                            $fcm->to($webTokens);
                            $fcm->notification($notification);
                            $fcm->send();
                        }
                    }    
                } else {
                    $user = User::all();
                    $user->each(function ($user) use ($title, $type, $message, $clickToSend) {
                        if($user->details && (($user->details->type == 2 && $user->is_premium == 1) || $user->details->type != 2)){
                            $user->notification_history()->create([
                                'title' => $title,
                                'code' => $type,
                                'status' => 'unread',
                                'message' => $message,
                            ]);

                            $tokenArray = new Collection();
                            $webTokenArray = new Collection();
                            $user->pushkey->each(function ($user_token_detail) use ($tokenArray, $webTokenArray) {
                                if($user_token_detail->device_token){
                                    if($user_token_detail->device_type == 'web'){
                                        $webTokenArray->push($user_token_detail->device_token);
                                    }else{
                                        $tokenArray->push($user_token_detail->device_token);
                                    }
                                }
                            });
                            $tokens = $tokenArray->unique()->values()->toArray();
                            $webTokens = $webTokenArray->unique()->values()->toArray();

                            $notification = array();
                            $notification['title'] = $title;
                            $notification['body'] = $message;
                            $notification['icon'] = self::ICON;
                            $notification['click_action'] = '';

                            $fcm = fcm();

                            $fcm->to($tokens);
                            $fcm->notification($notification);
                            $fcm->send();

                            //send to web with click action
                            if(count($webTokens) != 0){
                                $notification['click_action'] = $clickToSend;

                                $fcm->to($webTokens);
                                $fcm->notification($notification);
                                $fcm->send();
                            }
                        }
                    });
                }
            }

            if ($audition !== null || $user !== null) {
                if ($to == 'MANY') {
                    if ($type == 'custom') {

                        $repo = new UserAuditionsRepository(new UserAuditions());
                        /*
                         * Remove performer who got X'd via instant feedback [5Jan2020]
                         */
//                        $repData = $repo->getByParam('appointment_id', $audition->id);
                        $repData = $repo->findbyparams(['appointment_id'=> $audition->id, 'rejected' => 0]);


                        if ($repData->count() === 0) {
                            throw new \Exception('NULL ELEMENTS TO NOTIFICATE');
                        }
                        $repData->each(function ($useraudition) use ($title, $message, $type, $clickToSend) {
                            $tomsg = !empty($message) ? $message : $title;
                            $userRepo = new UserRepository(new User);
                            $user_result = $userRepo->find($useraudition->user_id);
                            if($user_result->details && (($user_result->details->type == 2 && $user_result->is_premium == 1) || $user_result->details->type != 2)){
                                $user_result->notification_history()->create([
                                    'title' => $title,
                                    'code' => $type,
                                    'status' => 'unread',
                                    'message' => $tomsg,
                                ]);

                                $tokenArray = new Collection();
                                $webTokenArray = new Collection();
                                $user_result->pushkey->each(function ($user_token_detail) use ($tokenArray, $webTokenArray) {
                                    if($user_token_detail->device_token){
                                        if($user_token_detail->device_type == 'web'){
                                            $webTokenArray->push($user_token_detail->device_token);
                                        }else{
                                            $tokenArray->push($user_token_detail->device_token);
                                        }
                                    }
                                });
                                $tokens = $tokenArray->unique()->values()->toArray();
                                $webTokens = $webTokenArray->unique()->values()->toArray();
                            
                                $notification = array();
                                $notification['title'] = $title;
                                $notification['body'] = $tomsg;
                                $notification['icon'] = self::ICON;
                                $notification['click_action'] = '';

                                $fcm = fcm();
                                $fcm->to($tokens);
                                $fcm->notification($notification);
                                $fcm->send();

                                //send to web with click action
                                if(count($webTokens) != 0){
                                    $notification['click_action'] = $clickToSend;

                                    $fcm->to($webTokens);
                                    $fcm->notification($notification);
                                    $fcm->send();
                                }
                            }
                        });
                    } elseif ($type == 'caster_to_performer') {
                        // dd($user);
                        if(is_array($user)){
                            $tokenArray = new Collection();
                            $webTokenArray = new Collection();
                            
                            foreach ($user as $key => $u) {
                                $userRepo = new UserRepository(new User);
                                $tomsg = !empty($message) ? $message : $title;
                                $user_result = $userRepo->find($u);

                                if($user_result && $user_result->details && (($user_result->details->type == 2 && $user_result->is_premium == 1) || $user_result->details->type != 2)){
                                    $user_result->pushkey->each(function ($user_token_detail) use ($tokenArray, $webTokenArray) {
                                        if($user_token_detail->device_token){
                                            if($user_token_detail->device_type == 'web'){
                                                $webTokenArray->push($user_token_detail->device_token);
                                            }else{
                                                $tokenArray->push($user_token_detail->device_token);
                                            }
                                        }
                                    });
                                }
                                
                                $history = $user_result->notification_history()->create([
                                    'title' => $title,
                                    'code' => $type,
                                    'status' => 'unread',
                                    'custom_data' => $u,
                                    'message' => $tomsg,
                                ]);
                                
                                $log->info($history);
                            }

                            $tokens = $tokenArray->unique()->values()->toArray();
                            $webTokens = $webTokenArray->unique()->values()->toArray();
                        
                            $notification = array();
                            $notification['title'] = $title;
                            $notification['body'] = $tomsg;
                            $notification['icon'] = self::ICON;
                            $notification['click_action'] = '';

                            $fcm = fcm();
                            $fcm->to($tokens);
                            $fcm->notification($notification);
                            $fcm->send();

                            //send to web with click action
                            if(count($webTokens) != 0){
                                $notification['click_action'] = $clickToSend;

                                $fcm->to($webTokens);
                                $fcm->notification($notification);
                                $fcm->send();
                            }
                        }
                    } else {
                        $audition->contributors->each(function ($contributor) use ($title, $message, $type, $audition, $log, $clickToSend) {
                            $userRepo = new UserRepository(new User);
                            $tomsg = !empty($message) ? $message : $title;
                            $user_result = $userRepo->find($contributor->user_id);
                            $history = $user_result->notification_history()->create([
                                'title' => $audition->title,
                                'code' => $type,
                                'status' => 'unread',
                                'custom_data' => $contributor->id,
                                'message' => $tomsg,
                            ]);
                            
                            $log->info($history);
                            
                            $tokenArray = new Collection();
                            $webTokenArray = new Collection();
                            $user_result->pushkey->each(function ($user_token_detail) use ($tokenArray, $webTokenArray) {
                                if($user_token_detail->device_token){
                                    if($user_token_detail->device_type == 'web'){
                                        $webTokenArray->push($user_token_detail->device_token);
                                    }else{
                                        $tokenArray->push($user_token_detail->device_token);
                                    }
                                }
                            });
                            $tokens = $tokenArray->unique()->values()->toArray();
                            $webTokens = $webTokenArray->unique()->values()->toArray();
                        
                            $notification = array();
                            $notification['title'] = $title;
                            $notification['body'] = $tomsg;
                            $notification['icon'] = self::ICON;
                            $notification['click_action'] = '';

                            $fcm = fcm();
                            $fcm->to($tokens);
                            $fcm->notification($notification);
                            $fcm->send();

                            //send to web with click action
                            if(count($webTokens) != 0){
                                $notification['click_action'] = $clickToSend;

                                $fcm->to($webTokens);
                                $fcm->notification($notification);
                                $fcm->send();
                            }
                        });
                    }
                } elseif ($to == 'ONE' && ($user instanceof User)) {
                    $user->notification_settings_on->each(function ($notification) use ($title, $message, $type, $user, $clickToSend, $audition) {
                        if($notification->code == $type && $notification->status == 'on'){
                            $user->notification_history()->create([
                                'title' => $title,
                                'code' => $type,
                                'status' => 'unread',
                                'message' => $message,
                            ]);
                        }

                        $tokenArray = new Collection();
                        $webTokenArray = new Collection();
                        $user->pushkey->each(function ($user_token_detail) use ($tokenArray, $webTokenArray) {
                            if($user_token_detail->device_token){
                                if($user_token_detail->device_type == 'web'){
                                    $webTokenArray->push($user_token_detail->device_token);
                                }else{
                                    $tokenArray->push($user_token_detail->device_token);
                                }
                            }
                        });
                        $tokens = $tokenArray->unique()->values()->toArray();
                        $webTokens = $webTokenArray->unique()->values()->toArray();
                    
                        $notification = array();
                        $notification['title'] = $title;
                        $notification['body'] = $message;
                        $notification['icon'] = self::ICON;
                        $notification['click_action'] = '';

                        $fcm = fcm();
                        $fcm->to($tokens);
                        $fcm->notification($notification);
                        $fcm->send();
                        
                        //send to web with click action
                        if(count($webTokens) != 0){
                            $notification['click_action'] = $clickToSend;

                            $fcm->to($webTokens);
                            $fcm->notification($notification);
                            $fcm->send();
                        }    
                    });
                } else if($to == 'ONLY_ONE_WITHOUT_CHECK' && ($user instanceof User)){
                    $tokenArray = new Collection();
                    $webTokenArray = new Collection();
                    $user->pushkey->each(function ($user_token_detail) use ($tokenArray, $webTokenArray) {
                        if($user_token_detail->device_token){
                            if($user_token_detail->device_type == 'web'){
                                $webTokenArray->push($user_token_detail->device_token);
                            }else{
                                $tokenArray->push($user_token_detail->device_token);
                            }
                        }
                    });
                    $tokens = $tokenArray->unique()->values()->toArray();
                    $webTokens = $webTokenArray->unique()->values()->toArray();
                   
                    $notification = array();
                    $notification['title'] = $title;
                    $notification['body'] = $message;
                    $notification['icon'] = self::ICON;
                    $notification['click_action'] = '';

                    $fcm = fcm();

                    $fcm->to($tokens);
                    if($type == self::INSTANT_FEEDBACK){
                        $fcm->data([
                            'type' => $type,
                            'appointment_id' => $appointment_id,
                            'performer_id' => $user->id
                        ]);
                    }
                    $fcm->notification($notification);
                    $fcm->send();

                    //send to web with click action
                    if(count($webTokens) != 0){
                        $notification['click_action'] = $clickToSend;

                        $fcm->to($webTokens);
                        $fcm->notification($notification);
                        $fcm->send();
                    }
                }
            }

        } catch (NotificationException $exception) {
            // $this->log->error($exception->getMessage());
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
