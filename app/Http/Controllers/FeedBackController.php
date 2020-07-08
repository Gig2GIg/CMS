<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
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
                $this->addFeedbackAddTrack($data);

                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointmentData = $appointmentRepo->find($request->appointment_id);
                if ($appointmentData->auditions->user_id === $request->evaluator) {
                    $slotRepo = new UserSlotsRepository(new UserSlots());
                    $slotData = $slotRepo->findbyparam('slots_id', $request->slot_id)->first();
                    if (isset($slotData)) {
                        $update = $slotData->update([
                            'favorite' => $request->favorite,
                        ]);
                    }
                }
                $this->addTalenteToDatabase($request->user);
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
            $oldFeedback = $feedbacks->where('user_id', $request->user_id)->first();
            $feedback = $feedbacks->where('user_id', $request->user_id)->first();

            $update = $feedback->update($data);

            $newFeedback = $feedbacks->where('user_id', $request->user_id)->first();

            if ($update) {
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

            foreach ($multidimensional_diff_old as $key => $value) {
                if($appointment && $appointment->auditions_id && $key != 'updated_at'){
                    $d = array();   
                    $d['audition_id'] = $appointment->auditions_id;
                    $d['edited_by'] = $this->getUserLogging();
                    $d['created_at'] = Carbon::now('UTC')->format('Y-m-d H:i:s');
                    $d['key'] = 'feedback_' . $key;
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
