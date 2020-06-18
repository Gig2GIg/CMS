<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Resources\FeedbackResource;
use App\Http\Requests\AddCommentRequest;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Performers;
use App\Models\UserSlots;
use App\Models\PerformersComment;
use Hashids\Hashids;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

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
            ];

            $repo = new FeedbackRepository(new Feedbacks());
            $data = $repo->create($data);
            if ($data->id) {
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
            ];
            
            $feedbackRepo = new FeedbackRepository(new Feedbacks());
            $feedbacks = $feedbackRepo->findbyparam('appointment_id', $request->id);
            $feedback = $feedbacks->where('user_id', $request->user_id)->first();
            
            $update = $feedback->update($data); 

            if ($update) {
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
            $repo = new PerformerRepository(new Performers());

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

            $count = $data->whereIn('director_id',$allIdsToInclude->unique()->values())->where('performer_id', $performer_id);

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
}
