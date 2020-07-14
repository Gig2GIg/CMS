<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\Notifications as SendNotifications;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Repositories\InstantFeedbackRepository;
use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\AuditionResponseInstantFeedback;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\InstantFeedback;
use App\Models\InstantFeedbackSettings;
use App\Models\Performers;
use App\Models\User;
use App\Models\UserSlots;
use App\Models\UserAuditions;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class InstantFeedbackController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(Request $request)
    {

        try {
            $data = [
                'appointment_id' => $request->appointment_id,
                'user_id' => $request->user,
                'evaluator_id' => $request->evaluator
            ];

            $repo = new InstantFeedbackRepository(new InstantFeedback());
            
            $exists = $repo->findbyparams($data)->get();
        
            if ($exists->count() == 0) {
                $data['comment'] = $request->comment;
                $data['suggested_appointment_id'] = $request->has('suggested_appointment_id') ? $request->suggested_appointment_id : NULL;
                
                $dataCreated = $repo->create($data);

                $userRepo = new UserRepository(new User());
                $user = $userRepo->find($request->user);

                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appoinmentData = $appointmentRepo->find($request->appointment_id);

                $auditionsRepo = new AuditionRepository(new Auditions());
                $audition = $auditionsRepo->find($appoinmentData->auditions_id);

                // if ($request->accepted == 0) {
                //     // remove that performer from group
                //     $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                //     $dataUserAuditions = $repoUserAuditions->all()
                //         ->where('user_id', $request->user)
                //         ->where('appointment_id', $request->appointment_id);

                //     if ($dataUserAuditions->count() > 0) {
                //         $data = $repoUserAuditions->findbyparams(
                //             [
                //                 'user_id' => $request->user,
                //                 'appointment_id' => $request->appointment_id
                //             ]
                //         );
                //         $updateAuditionsData = $data->update(['group_no' => 0]);
                //     }
                // }
                
                if($request->comment != ""){
                    $comment = $request->comment;    
                }else{
                    $comment = "";
                }

                if ($request->accepted == 0) {
                    // remove that performer from group
                    $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                    $dataUserAuditions = $repoUserAuditions->all()
                        ->where('user_id', $request->user)
                        ->where('appointment_id', $request->appointment_id);

                    if ($dataUserAuditions->count() > 0) {
                        $updateAuditionsData = DB::table('user_auditions')
                            ->where('user_id', $request->user)
                            ->where('appointment_id', $request->appointment_id)
                            ->update([
                                'group_no' => 0,
                                'rejected' => 1,
                            ]);
                    }

                    if($user->details && (($user->details->type == 2 && $user->is_premium == 1) || $user->details->type != 2)){
                        // send notification
                        $this->sendStoreNotificationToUser($user, $audition, $comment, $request->appointment_id);
                    }
                    $this->saveStoreNotificationToUser($user, $audition, $comment);
                } elseif ($request->accepted == 2) {
                    // $appointmentRepo = new AppointmentRepository(new Appointments());
                    // $appointmentData = $appointmentRepo->find($request->appointment_id);

                    // $evalUser = User::find($request->evaluator);
                    // $allIdsToInclude = array();
                    
                    // array_push($allIdsToInclude, $request->evaluator);
                    // //It is to fetch other user's data conidering if logged in user is an invited user
                    // if($evalUser->invited_by != NULL){
                    //     array_push($allIdsToInclude, $evalUser->invited_by);
                    // } else {
                    //     $invitedUserIds = User::where('invited_by', $request->evaluator)->get()->pluck('id');
                    //     array_merge($allIdsToInclude, $invitedUserIds->toArray());
                    // }

                    // if (in_array($appointmentData->auditions->user_id, $allIdsToInclude))
                    // {

                    $slotRepo = new UserSlotsRepository(new UserSlots());
                    $condition = array();
                    if($request->has('slots_id') && ($request->slot_id != null || $request->slot_id != '')){
                        $condition['slots_id'] = $request->slot_id;
                    }
                    $condition['appointment_id'] = $request->appointment_id;
                    $condition['user_id'] = $request->user;

                    $slotData = $slotRepo->findbyparams($condition)->first();

                    if (isset($slotData) && $slotData->future_kept == 0) {
                        $update = $slotData->update([
                            'future_kept' => 1,
                        ]);
                    } else {
                        $dataResponse = ['data' => trans('messages.already_kept_future')];
                        $code = 406;

                        return response()->json($dataResponse, $code);
                    }
                    // }

                    if($user->details && (($user->details->type == 2 && $user->is_premium == 1) || $user->details->type != 2)){
                        // send notification
                        $this->sendStoreNotificationToUser($user, $audition, $comment, $request->appointment_id);
                    }
                    $this->saveStoreNotificationToUser($user, $audition, $comment); 
                } else {
                    if($user->details && (($user->details->type == 2 && $user->is_premium == 1) || $user->details->type != 2)){
                        // send notification
                        $this->sendStoreNotificationToUser($user, $audition, $comment, $request->appointment_id);
                    }
                    $this->saveStoreNotificationToUser($user, $audition, $comment);
                }

                $this->addTalenteToDatabase($request->user);

                $dataResponse = ['data' => trans('messages.feedback_save_success'), 'feedback_id' => $dataCreated->id];
                $code = 201;
            } else {
                $dataResponse = ['data' => 'Feedback already submitted'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.feedback_not_add')], 406);
            // return response()->json(['data' => 'Feedback not add'], 406);
        }
    }

    public function restore(Request $request)
    {

        try {
            $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
            $dataUserAuditions = $repoUserAuditions->findbyparams(
                [
                    'user_id' => $request->user,
                    'appointment_id' => $request->appointment_id
                ])->first();

            $dataUserAuditions->rejected = 0;

            $dataUserAuditions->save();

            $instantFeedbackRepo = new InstantFeedbackRepository(new InstantFeedback());

            $instantFeedbackRepo->findbyparams(
                [
                    'user_id' => $request->user,
                    'appointment_id' => $request->appointment_id
                ])->delete();

            $dataResponse = ['data' => trans('messages.performer_restored_success')];
            $code = 200;

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.performer_restored_failure')], 406);
        }
    }

    public function search_with_upcoming_audition(Request $request)
    {
        if (!is_null($request->value)) {
            $auditionRepo = new Auditions();
            $data = $auditionRepo
                ->where('title', 'like', "%{$request->value}%")
                ->where('status', 1)
                ->where('user_id', $this->getUserLogging())
                ->get();
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function getDefaultInstantFeedback(Request $request)
    {
        if (!is_null($request->user_id)) {
            $instant_feedback_settings = new InstantFeedbackSettings();
            $data = $instant_feedback_settings->where('user_id', $request->user_id)->get();
            $response = array();
            
            if ($data == null || count($data) == 0) {
                $response['comment'] = trans('messages.default_instant_feedback_message');
                $response['positiveComment'] = trans('messages.default_instant_feedback_message');
            } else {
                foreach($data as $e){
                    if($e->type == 'positive')
                        $response['positiveComment'] = $e->comment;
                    else    
                        $response['comment'] = $e->comment;
                }
            }

            return response()->json(['data' => $response], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function updatDefaultInstantFeedback(Request $request)
    {
        if($request->type && $request->type != null && ($request->type == 'positive' || $request->type == 'negative')){
            $type = $request->type;
        }else{
            $type = 'negative';
        }
        
        if (!is_null($request->feedback)) {
            $instant_feedback_settings = new InstantFeedbackSettings();

            $data = $instant_feedback_settings
                    ->where('user_id', $this->getUserLogging())
                    ->where('type', $type)
                    ->get()->toArray();

            if (empty($data)) {
                // add if no feedback added for caster
                $data = [
                    'comment' => $request->feedback,
                    'user_id' => $this->getUserLogging(),
                    'type' => $type
                ];

                $feedbackUpdated = $instant_feedback_settings->insert($data);
            } else {
                // update if caster's feedbacke already exist
                $update_feedback = $instant_feedback_settings
                    ->where('user_id', $this->getUserLogging())
                    ->where('type', $type)
                    ->update(['comment' => $request->feedback]);
            }

            $response = $instant_feedback_settings->where('user_id', $this->getUserLogging())->get()->toArray();
            return response()->json(['data' => $response], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }     
    }

    public function instantFeedbackDetailsCaster(Request $request)
    {
        try {
            $repoFeedback = new InstantFeedbackRepository(new InstantFeedback());

            $feedbacks = $repoFeedback->all()->where('appointment_id', $request->id)
                ->where('evaluator_id', '=', $this->getUserLogging())->where('user_id', '=', $request->user_id)->first();

            if ($feedbacks == null) {
                throw new \Exception('Data not found');
            }

            $dataResponse = ['data' => $feedbacks];
            $code = 200;

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Data Not Found'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function instantFeedbackDetailsPerformer(Request $request)
    {
        try {
            $repoFeedback = new InstantFeedbackRepository(new InstantFeedback());

            $data = new stdClass();
            $feedbacks = $repoFeedback->all()
                ->where('appointment_id', $request->id)
                ->where('user_id', '=', $request->user_id)
                ->first();

            if ($feedbacks == null) {
                throw new \Exception('Data not found');
            }

            if ($feedbacks == null) {
                throw new \Exception('Data not found');
            }
            $appointmentRepo = new AppointmentRepository(new Appointments());
            $auditionsRepo = new AuditionRepository(new Auditions());

            $appoinmentData = $appointmentRepo->find($request->id);
            $auditionData = $auditionsRepo->all()->where('id', $appoinmentData->auditions_id);
            $responseDataAudition = AuditionResponseInstantFeedback::collection($auditionData);

            $data->feedback = $feedbacks;
            $data->audition = $responseDataAudition;

            if ($feedbacks->suggested_appointment_id != null) {
//                $suggestedAppoinmentData = $appointmentRepo->find($feedbacks->suggested_appointment_id);
                $suggestedAuditionData = $auditionsRepo->all()->where('id', $feedbacks->suggested_appointment_id);
                $responseDataSuggestedAudition = AuditionResponseInstantFeedback::collection($suggestedAuditionData);
                $data->suggested_audition = $responseDataSuggestedAudition;
            }
            // $data->suggested_audition = isset($responseDataSuggestedAudition) ? $responseDataSuggestedAudition : array();

            $dataResponse = ['data' => $data];
            $code = 200;

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Data Not Found'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function saveStoreNotificationToUser($user, $audition, $comment = ""): void
    {
        try {
            if($comment == ""){
                $message = 'You have received new instant feedback for ' . $audition->title;
            }else{
                $message = $comment;
            }

            if ($user instanceof User) {
                $history = $user->notification_history()->create([
                    'title' => $audition->title,
                    'code' => 'instant_feedback',
                    'status' => 'unread',
                    'message' => $message,
                ]);
                $this->log->info('saveStoreNotificationToUser:: ', $history);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function sendStoreNotificationToUser($user, $audition, $comment = "", $appointment_id = null): void
    {
        try {
            if($comment == ""){
                $message = 'You have received new instant feedback for ' . $audition->title;
            }else{
                $message = $comment;
            }
            
            $this->sendPushNotification(
                $audition,
                SendNotifications::INSTANT_FEEDBACK,
                $user,
                $appointment_id,
                $message
            );

        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function addTalenteToDatabase($performer_id)
    {
        try {
            $hasid = new Hashids('g2g');
            $dateHash = new \DateTime();
            $dataTime = $dateHash->getTimestamp();
            $repo = new Performers();

            $user = Auth::user();            
            
            //it is to fetch logged in user's invited users data if any
            $userRepo = new User();
            $invitedUserIds = $userRepo->where('invited_by', $this->getUserLogging())->get()->pluck('id');

            //It is to fetch other user's data conidering if logged in user is an invited user
            if($user->invited_by != NULL){
                $allInvitedUsersOfAdminIds = $userRepo->where('invited_by', $user->invited_by)->get()->pluck('id');

                //pushing invited_by ID in array too
                $allInvitedUsersOfAdminIds->push($user->invited_by); 

                $allIdsToInclude = $invitedUserIds->merge($allInvitedUsersOfAdminIds);
            }else{
                $allIdsToInclude = $invitedUserIds;
            }

            //pushing own ID into WHERE IN constraint
            $allIdsToInclude->push($this->getUserLogging()); 

            $count = $repo->whereIn('director_id',$allIdsToInclude->unique()->values())->where('performer_id', $performer_id);

            if ($count->count() > 0) {
                throw new \Exception("User exists in your database");
            }
            $register = [
                'performer_id' => $performer_id,
                'director_id' => $this->getUserLogging(),
                'uuid' => $hasid->encode($performer_id, $dataTime),
            ];

            $repo->create($register);
            $this->log->info('Talent add');
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
        }
    }

}
