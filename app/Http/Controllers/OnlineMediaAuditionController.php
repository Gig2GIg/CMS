<?php

namespace App\Http\Controllers;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Controllers\Utils\Notifications as SendNotifications;
use App\Http\Exceptions\NotificationException;
use App\Http\Repositories\MediaOnlineRepository;
use App\Models\OnlineMediaAudition;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\UserAuditions;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Slots;
use Carbon\Carbon;

class OnlineMediaAuditionController extends Controller
{
    public function create(Request $request)
    {
        try {
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $data = [
                'type' => $request->type,
                'url' => $request->url,
                'thumbnail' => $request->thumbnail,
                'name' => $request->name,
                'appointment_id' => $request->appointment_id,
                'performer_id' => $this->getUserLogging()
            ];

            $appointment = Appointments::find($request->appointment_id);
            $audition = Auditions::find($appointment->auditions_id);

            if($audition->end_date > Carbon::now('UTC')->format('Y-m-d'))
            {
                $res = $repo->create($data);
                if (is_null($res->id)) {
                    throw new CreateException('media not created');
                }

                $userRequest = UserAuditions::where(['appointment_id' => $request->appointment_id, "user_id" => $this->getUserLogging()])->first(); 

                UserSlots::updateOrCreate([
                    'user_id' => $this->getUserLogging(),
                    'appointment_id' => $request->appointment_id,
                    'roles_id' => $userRequest->rol_id,
                    'status' => 2
                ],[
                    'user_id' => $this->getUserLogging(),
                    'appointment_id' => $request->appointment_id,
                    'slots_id' => factory(Slots::class)->create([
                        'appointment_id' => $request->appointment_id,
                        'time' => "00:00",
                        'status' => false,
                    ])->id,
                    'roles_id' => $userRequest->rol_id,
                    'status' => 2
                ]);    

                try {
                    $cuser = User::find($audition->user_id);

                    if($cuser && $cuser->details && (($cuser->details->type == 2 && $cuser->is_premium == 1) || $cuser->details->type != 2)){
                        $this->sendStoreNotificationToUser($cuser, $audition);
                    }
                    $this->saveStoreNotificationToUser($cuser, $audition);

                    //send push to admin about new media uploaded
                    if($cuser->invited_by != NULL){
                        $auser = User::find($auser->invited_by);

                        if($auser && $auser->details && (($auser->details->type == 2 && $auser->is_premium == 1) || $auser->details->type != 2)){
                            $this->sendStoreNotificationToUser($auser, $audition);
                        }
                        $this->saveStoreNotificationToUser($auser, $audition);
                    }

                } catch (NotificationException $exception) {
                    $this->log->error($exception->getMessage());
                }

                return response()->json([
                    'message' => trans('messages.media_created'),
                    'data' => $res
                ], 201);
            } else {
                return response()->json([
                    'message' => trans('messages.online_audition_past'),
                    'data' => []
                ], 400);
            }
        } catch (\Exception $exception) {
            $this->log->error("ONLINEMEDIA:: " . $exception->getMessage());
            $this->log->error("ONLINEMEDIA:: " . $exception->getLine());
            return response()->json([
                'message' => trans('messages.media_not_created'),
                'data' => []
            ], 405);
        }
    }

    public function listByUser(Request $request)
    {
        try {
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $dataUser = $repo->findbyparam('performer_id', $request->performer_id)->get();
            $data = $dataUser->where('appointment_id', $request->appointment_id);
            if ($data->count() == 0) {
                throw new NotFoundException('media not found');
            }

            return response()->json([
                'message' => 'Media for user: ' . $request->performer_id,
                'data' => $data
            ], 200);
        } catch (\Exception $exception) {
            $this->log->error("ONLINEMEDIA:: " . $exception->getMessage());
            $this->log->error("ONLINEMEDIA:: " . $exception->getLine());
            return response()->json([
                'message' => trans('messages.media_not_found'),
                'data' => []
            ], 404);
        }
    }

    public function listByRound(Request $request)
    {
        try {
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $data = $repo->findbyparam('appointment_id', $request->appointment_id)->get();

            if ($data->count() == 0) {
                throw new NotFoundException('media not found');
            }

            return response()->json([
                'message' => 'Media for round: ' . $request->appointment_id,
                'data' => $data
            ], 200);
        } catch (\Exception $exception) {
            $this->log->error("ONLINEMEDIA:: " . $exception->getMessage());
            $this->log->error("ONLINEMEDIA:: " . $exception->getLine());
            return response()->json([
                'message' => trans('messages.media_not_found'),
                'data' => []
            ], 404);
        }
    }

    public function get(Request $request)
    {
        try {
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $data = $repo->find($request->id);

            if ($data->count() == 0) {
                throw new NotFoundException('media not found');
            }

            return response()->json([
                'message' => 'Media for id: ' . $request->id,
                'data' => $data
            ], 200);
        } catch (\Exception $exception) {
            $this->log->error("ONLINEMEDIA:: " . $exception->getMessage());
            $this->log->error("ONLINEMEDIA:: " . $exception->getLine());
            return response()->json([
                'message' => trans('messages.media_not_found'),
                'data' => []
            ], 404);
        }
    }

    public function saveStoreNotificationToUser($user, $audition): void
    {
        try {
            if ($user instanceof User) {
                $history = $user->notification_history()->create([
                    'title' => 'New media has been submitted in the audition ' . $audition->title,
                    'code' => 'new_online_media',
                    'status' => 'unread',
                    'message' => 'New media has been submitted in the audition ' . $audition->title
                ]);
                $this->log->info('saveStoreNotificationToUser:: ', $history);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function sendStoreNotificationToUser($user, $audition): void
    {
        try {

            $this->sendPushNotification(
                $audition,
                SendNotifications::NEW_ONLINE_MEDIA,
                $user,
                $audition->title,
                'New media has been submitted in the audition ' . $audition->title
            );
            
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }   
}
