<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\Notifications as SendNotifications;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Resources\FeedbackResource;
use App\Http\Requests\AddCommentRequest;
use App\Http\Requests\KeepForFutureRequest;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Performers;
use App\Models\UserSlots;
use App\Models\PerformersComment;
use App\Models\AuditionLog;
use Hashids\Hashids;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FeedBackController extends Controller
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
            $userExists = false;
            $evaluatorExits = false;
            $slotExits = false;

            if($request->callback === true){
                $request->callback = 1;
            }else if($request->callback === false){
                $request->callback = 0;
            }else if($request->callback === null){
                $request->callback = null; 
            }else{
                $request->callback = null; 
            }

            $data = [
                'appointment_id' => $request->appointment_id,
                'user_id' => $request->user, //id usuario que recibe evaluacion
                'evaluator_id' => $request->evaluator && $request->evaluator != null && $request->evaluator != "" ? $request->evaluator : null, //id de usuario que da feecback,
                'evaluation' => $request->evaluation && $request->evaluation != null && $request->evaluation != "" ? $request->evaluation : null,
                'callback' => $request->callback,
                'work' => $request->work && $request->work != null && $request->work != "" ? $request->work : null,
                'favorite' => $request->favorite,
                'slot_id' => $request->slot_id && $request->slot_id != null && $request->slot_id != "" ? $request->slot_id : null,
                'comment' => $request->comment && $request->comment != null && $request->comment != "" ? $request->comment : null,
                'rating' => $request->rating && $request->rating != null && $request->rating != "" ? $request->rating : null,
            ];

            $repo = new FeedbackRepository(new Feedbacks());
            $data = $repo->create($data);

            if ($data->id) {
                $user = User::find($request->user);
                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointmentData = $appointmentRepo->find($request->appointment_id);
                $auditionsRepo = new AuditionRepository(new Auditions());
                $audition = $appointmentData ? $auditionsRepo->find($appointmentData->auditions_id) : NULL;
                if($user && $audition && $user->details && (($user->details->type == 2 && $user->is_premium == 1) || $user->details->type != 2)){
                    // send notification
                    $this->sendStoreNotificationToUser($user, $audition, "", $request->appointment_id);
                }
                $this->saveStoreNotificationToUser($user, $audition, "");
                //$this->addFeedbackAddTrack($data);

                if ($appointmentData->auditions->user_id === $request->evaluator) {
                    $slotRepo = new UserSlotsRepository(new UserSlots());
                    $slotData = $slotRepo->findbyparam('slots_id', $request->slot_id)->first();
                    if (isset($slotData)) {
                        $update = $slotData->update([
                            'favorite' => $request->favorite,
                        ]);
                    }
                }
                // $this->addTalenteToDatabase($request->user);
                $dataResponse = ['data' => 'Feedback saved successfully', 'feedback_id' => $data->id];
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

    public function update(Request $request)
    {
        try {
            $userExists = false;
            $evaluatorExits = false;
            $slotExits = false;

            if($request->callback === true){
                $request->callback = 1;
            }else if($request->callback === false){
                $request->callback = 0;
            }else if($request->callback === null){
                $request->callback = null; 
            }else{
                $request->callback = null; 
            }

            $data = [
                'evaluation' => $request->evaluation && $request->evaluation != null && $request->evaluation != "" ? $request->evaluation : null,
                'callback' => $request->callback,
                'work' => $request->work && $request->work != null && $request->work != "" ? $request->work : null,
                'favorite' => $request->favorite,
                'comment' => $request->comment && $request->comment != null && $request->comment != "" ? $request->comment : null,
                'rating' => $request->rating && $request->rating != null && $request->rating != "" ? $request->rating : null,
            ];
            
            $feedbackRepo = new FeedbackRepository(new Feedbacks());
            $feedbacks = $feedbackRepo->findbyparam('appointment_id', $request->id);
            $oldFeedback = $feedbacks->where('user_id', $request->user_id)->where('evaluator_id', $request->evaluator)->first();
            $feedback = $feedbacks->where('user_id', $request->user_id)->where('evaluator_id', $request->evaluator)->first();

            $update = $feedback->update($data);

            $newFeedback = $feedbacks->where('user_id', $request->user_id)->first();

            if ($update) {
                $user = User::find($request->user_id);
                $repoAppointment = new AppointmentRepository(new Appointments());
                $appointment = $repoAppointment->find($request->id);
                $auditionsRepo = new AuditionRepository(new Auditions());
                $audition = $appointment ? $auditionsRepo->find($appointment->auditions_id) : NULL;
                if($audition){
                    $comment = 'Your feedback has been updated for ' . $audition->title;
                    $this->saveStoreNotificationToUser($user, $audition, $comment);
                }else{
                    $comment = 'Your feedback has been updated';
                    $this->saveStoreNotificationToUser($user, NULL, $comment);
                }

                if($user && $audition && $user->details && (($user->details->type == 2 && $user->is_premium == 1) || $user->details->type != 2)){
                    // send notification
                    $this->sendStoreNotificationToUser($user, $audition, $comment, $request->id);
                }

                $this->updateFeedbackAddTrack($oldFeedback, $newFeedback);
                $dataResponse = ['data' => 'Feedback update'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Feedback not update'];
                $code = 422;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.feedback_not_update')], 422);
            // return response()->json(['data' => 'Feedback not update'], 422);
        }
    }

    function list(Request $request) {
        try {
            $repo = new FeedbackRepository(new Feedbacks());
            $data = $repo->findbyparam('appointment_id', $request->appointment_id);
            $dataPre = $data->where('user_id', '=', $request->performer)->get();
            
            if ($dataPre->count() > 0) {
                $dataResponse = ['data' => FeedbackResource::collection($dataPre)];
                $code = 200;
            } else {
                $dataResponse = ['data' => []];
                $code = 200;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

    public function finalUserFeedback(Request $request)
    {
        try {
            $repo = new FeedbackRepository(new Feedbacks());
            $repoAppointment = new AppointmentRepository(new Appointments());
            $dataRepo = $repoAppointment->find($request->id);
            $data = $repo->findbyparam('appointment_id', $request->id);

            $dataPre = $data->where('user_id', '=', $this->getUserLogging())
            // ->where('evaluator_id', '=', $dataRepo->auditions->user_id)
                ->first() ?? new Collection();
            if ($dataPre->count() > 0) {
                $dataResponse = ['data' => new FeedbackResource($dataPre)];
                $code = 200;
            } else {
                $dataResponse = ['data' => []];
                $code = 200;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

    public function feedbackDetailsByUser(Request $request)
    {
        try {
            $repo = new FeedbackRepository(new Feedbacks());
            $data = $repo->findbyparams(
                [
                    'appointment_id' => $request->id,
                    'evaluator_id' => $this->getUserLogging(),
                    'user_id' => $request->user_id,
                ]

            );
            $feedbacks = $data->first();

            if (empty($feedbacks)) {
                return response()->json(['data' => trans('messages.data_not_found')], 404);
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

    public function addIndividualComment(AddCommentRequest $request)
    {
        try {

            $data = [
                'appointment_id' => $request->appointment_id,
                'user_id' => $request->user_id,
                'evaluator_id' => $this->getUserLogging(),
                'slot_id' => $request->slot_id && $request->slot_id != null && $request->slot_id != "" ? $request->slot_id : null,
                'comment' => $request->comment && $request->comment != null && $request->comment != "" ? $request->comment : null,
            ];

            $repo = new PerformersComment();
            $data = $repo->create($data);
            
            $dataResponse = ['data' => trans('messages.comment_added'), 'comment' => $data];
            $code = 200;
           
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.comment_not_added')], 406);
            // return response()->json(['data' => 'Feedback not add'], 406);

        }
    }

    public function addFeedbackAddTrack($data = null){
        try {
            $data = $data->toArray();

            $insertData = array();
            $repoAppointment = new AppointmentRepository(new Appointments());
            $appointment = $repoAppointment->find($data['appointment_id']);

            foreach ($data as $key => $value) {
                if($appointment && $appointment->auditions_id && $key != 'id' && $key != 'created_at' && $key != 'updated_at'){
                    $d = array();   
                    $d['audition_id'] = $appointment->auditions_id;
                    $d['edited_by'] = $this->getUserLogging();
                    $d['created_at'] = Carbon::now('UTC')->format('Y-m-d H:i:s');
                    $d['key'] = 'feedback_' . $key;
                    $d['old_value'] = null;
                    $d['new_value'] = $value;

                    array_push($insertData, $d);   
                }
            }

            AuditionLog::insert($insertData);
           
            return true;
        } catch (\Exception $exception) {
            $this->log->error("ERR IN ADDING TRACK OF FEEDBACK ADD::: " . $exception->getMessage());
            return true;
        }
    }

     public function saveStoreNotificationToUser($user, $audition, $comment = ""): void
    {
        try {
            if($comment == ""){
                $message = 'You have received new feedback for ' . $audition->title;
            }else{
                $message = $comment;
            }

            if(!$audition){
                $title = 'Feedback Notification';
            }else{
                $title = $audition->title;
            }

            if ($user instanceof User) {
                $history = $user->notification_history()->create([
                    'title' => $title,
                    'code' => 'feedback',
                    'status' => 'unread',
                    'message' => $message
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
                $message = 'You have received new feedback for ' . $audition->title;
            }else{
                $message = $comment;
            }
            
            $this->sendPushNotification(
                $audition,
                SendNotifications::FEEDBACK,
                $user,
                $appointment_id,
                $message
            );

        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function updateFeedbackAddTrack($oldData = null, $newData = null){
        try {
            $oldData = $oldData->toArray();
            $newData = $newData->toArray();

            //checking diff in two arrays old and new
            $diff_old = array_diff(array_map('serialize', $oldData), array_map('serialize', $newData));
            $diff_new = array_diff(array_map('serialize', $newData), array_map('serialize', $oldData));
            $multidimensional_diff_old = array_map('unserialize', $diff_old);
            $multidimensional_diff_new = array_map('unserialize', $diff_new);

            $insertData = array();
            $repoAppointment = new AppointmentRepository(new Appointments());
            $appointment = $repoAppointment->find($oldData['appointment_id']);

            if(count($multidimensional_diff_old) > 0 && $appointment && $appointment->auditions_id){
                $performer = User::find($oldData['user_id'])->details;
                $roundData = [
                    [
                        'audition_id' => $appointment->auditions_id,
                        'edited_by' => $this->getUserLogging(),
                        'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                        'key' => 'Feedback Round',
                        'old_value' => null,
                        'new_value' => 'Round ' . $appointment->round
                    ],
                    [
                        'audition_id' => $appointment->auditions_id,
                        'edited_by' => $this->getUserLogging(),
                        'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                        'key' => 'Feedback Performer',
                        'old_value' => null,
                        'new_value' => $performer ? $performer->first_name . ' ' . $performer->last_name : $oldData['user_id']
                    ]
                ];
                
                AuditionLog::insert($roundData);

                if(isset($oldData['favorite']) && $oldData['favorite'] != $newData['favorite']){
                    AuditionLog::insert([
                        'audition_id' => $appointment->auditions_id,
                        'edited_by' => $this->getUserLogging(),
                        'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                        'key' => 'Feedback Starred',
                        'old_value' => $oldData['favorite'] == 1 ? 'Yes' : 'No',
                        'new_value' => $newData['favorite'] == 1 ? 'Yes' : 'No'
                    ]);
                }

                if(isset($oldData['callback']) && $oldData['callback'] != $newData['callback']){
                    AuditionLog::insert([
                        'audition_id' => $appointment->auditions_id,
                        'edited_by' => $this->getUserLogging(),
                        'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                        'key' => 'Feedback Callback',
                        'old_value' => $oldData['callback'] == 1 ? 'Yes' : 'No',
                        'new_value' => $newData['callback'] == 1 ? 'Yes' : 'No'
                    ]);
                }

            }
            foreach ($multidimensional_diff_old as $key => $value) {
                if($appointment && $appointment->auditions_id && $key != 'updated_at' && $key != 'slot_id' && $key != 'evaluator_id' && $key == 'callback' && $key == 'favorite'){
                    $d = array();  
                    $d['audition_id'] = $appointment->auditions_id;
                    $d['edited_by'] = $this->getUserLogging();
                    $d['created_at'] = Carbon::now('UTC')->format('Y-m-d H:i:s');
                    $d['key'] = str_replace('_', ' ', 'Feedback ' . ucwords(strtolower($key)));
                    $d['old_value'] = $value;
                    $d['new_value'] = $multidimensional_diff_new[$key];

                    array_push($insertData, $d);   
                }
            }

            AuditionLog::insert($insertData);
           
            return true;
        } catch (\Exception $exception) {
            $this->log->error("ERR IN UPDATING TRACK OF FEEDBACK ADD::: " . $exception->getMessage());
            return true;
        }
    }
}
