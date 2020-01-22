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
    const UPCOMING_AUDITION = 'upcoming_audition';
    const REPRESENTATION_EMAIL = 'representation_email';
    const DOCUMENT_UPLOAD = 'document_upload';
    const CHECK_IN = 'check_in';
    const AUTIDION_REQUEST = 'autidion_request';
    const CUSTOM = 'custom';
    const CMS = 'cms';
    const CMS_TO_USER = 'cms_to_user';
    const ICON = '/images/logo-color-push.png';

    public static function send($audition = null, $type, $user = null, $title = null, $message = null, $clickToSend = "")
    {
        try {

            $log = new LogManger();
            switch ($type) {
                case self::AUTIDION_ADD_CONTRIBUIDOR:
                    $log->info("PUSH NOTIFICATION AUDITION SAVE " . $audition->title);
                    $title = 'Audition Save';
                    // $message = 'You have been added to the audition ' . $audition->title;
                    $message = 'Contributor invitation available for ' . $audition->title;
                    $to = 'MANY';
                    $clickToSend = env('CASTER_URL');
                    break;
                case self::UPCOMING_AUDITION:
                    $log->info("PUSH NOTIFICATION UPCOMMING " . $audition->title);
                    $title = 'Audition Upcomming';
                    $message = ' you have been upcoming to audition ' . $audition->title;
                    $to = 'ONE';
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
                case self::DOCUMENT_UPLOAD:
                    $log->info("PUSH NOTIFICATION DOCUMENT_UPLOAD");
                    $title = 'Document Upload';
                    $message = "Some message";
                    $to = 'ONE';
                    $clickToSend = env('PERFORMER_URL');
                    break;
                case self::CHECK_IN:
                    $log->info("PUSH NOTIFICATION  CHECK_IN " . $audition->title);
                    $title = 'Check-in ';
                    $message = 'you have been registered for the audition ' . $audition->title;
                    $to = 'ONE';
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
                default:
            }

            if ($type == 'cms' || $type == 'cms_to_user') {
                if ($to == 'ONE') {
                    $user->notification_history()->create([
                        'title' => $title,
                        'code' => $type,
                        'status' => 'unread',
                        'message' => $title,
                    ]);

                    $tokenArray = new Collection();
                    $user->pushkey->each(function ($user_token_detail) use ($tokenArray) {
                        if($user_token_detail->device_token){
                            $tokenArray->push($user_token_detail->device_token);
                        }
                    });
                    $tokens = $tokenArray->unique()->toArray();

                    fcm()
                        ->to($tokens)
                        ->notification([
                            'title' => $title,
                            'body' => $title,
                            'icon' => self::ICON,
                            'click_action' => $clickToSend
                        ])
                        ->send();
                } else {
                    $user = User::all();
                    $user->each(function ($user) use ($title, $type, $message) {
                        $user->notification_history()->create([
                            'title' => $title,
                            'code' => $type,
                            'status' => 'unread',
                            'message' => $message,
                        ]);

                        $tokenArray = new Collection();
                        $user->pushkey->each(function ($user_token_detail) use ($tokenArray) {
                            if($user_token_detail->device_token){
                                $tokenArray->push($user_token_detail->device_token);
                            }
                        });
                        $tokens = $tokenArray->unique()->toArray();

                        fcm()
                            ->to($tokens)
                            ->notification([
                                'title' => $title,
                                'body' => $message,
                                'icon' => self::ICON,
                                'click_action' => $clickToSend
                            ])
                            ->send();
                    });
                }
            }

            if ($audition !== null || $user !== null) {
                if ($to == 'MANY') {
                    if ($type == 'custom') {

                        $repo = new UserAuditionsRepository(new UserAuditions());
                        $repData = $repo->getByParam('appointment_id', $audition->id);
                        if ($repData->count() === 0) {
                            throw new \Exception('NULL ELEMENETS TO NOTIFICATE');
                        }
                        $repData->each(function ($useraudition) use ($title, $message, $type) {
                            $tomsg = !empty($message) ? $message : $title;
                            $userRepo = new UserRepository(new User);
                            $user_result = $userRepo->find($useraudition->user_id);
                            $user_result->notification_history()->create([
                                'title' => $title,
                                'code' => $type,
                                'status' => 'unread',
                                'message' => $tomsg,
                            ]);

                            $tokenArray = new Collection();
                            $user_result->pushkey->each(function ($user_token_detail) use ($tokenArray) {
                                if($user_token_detail->device_token){
                                    $tokenArray->push($user_token_detail->device_token);
                                }
                            });
                            $tokens = $tokenArray->unique()->toArray();

                            fcm()
                                ->to($tokens)
                                ->notification([
                                    'title' => $title,
                                    'body' => $tomsg,
                                    'icon' => self::ICON,
                                    'click_action' => $clickToSend
                                ])
                                ->send();
                        });
                    } else {
                        $audition->contributors->each(function ($contributor) use ($title, $message, $type, $audition, $log) {
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

                            $tokenArray = new Collection();
                            $user_result->pushkey->each(function ($user_token_detail) use ($tokenArray) {
                                if($user_token_detail->device_token){
                                    $tokenArray->push($user_token_detail->device_token);
                                }
                            });
                            $tokens = $tokenArray->unique()->toArray();

                            $log->info($history);
                            fcm()
                                ->to($tokens)
                                ->notification([
                                    'title' => $title,
                                    'body' => $tomsg,
                                    'icon' => self::ICON,
                                    'click_action' => $clickToSend
                                ])
                                ->send();
                        });
                    }
                } elseif ($to == 'ONE' && ($user instanceof User)) {
                    $user->notification_settings_on->each(function ($notification) use ($title, $message, $type, $user) {
                        if ($notification->code == $type && $notification->status == 'on') {
                            $user->notification_history()->create([
                                'title' => $title,
                                'code' => $type,
                                'status' => 'unread',
                                'message' => $message,
                            ]);
                        }
                        $tokenArray = new Collection();
                        $user->pushkey->each(function ($user_token_detail) use ($tokenArray) {
                            if($user_token_detail->device_token){
                                $tokenArray->push($user_token_detail->device_token);
                            }
                        });
                        $tokens = $tokenArray->unique()->toArray();

                        fcm()
                            ->to($tokens)
                            ->notification([
                                'title' => $title,
                                'body' => $message,
                                'icon' => self::ICON,
                                'click_action' => $clickToSend
                            ])
                            ->send();
                    });
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
