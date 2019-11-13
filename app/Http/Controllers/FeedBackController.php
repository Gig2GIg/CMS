<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Resources\FeedbackResource;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Performers;
use App\Models\Slots;
use App\Models\UserAuditions;
use App\Models\UserSlots;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

            $data = [
                'appointment_id' => $request->appointment_id,
                'user_id' => $request->user, //id usuario que recibe evaluacion
                'evaluator_id' => $request->evaluator,//id de usuario que da feecback,
                'evaluation' => $request->evaluation,
                'callback' => $request->callback,
                'work' => $request->work,
                'favorite' => $request->favorite,
                'slot_id'=>$request->slot_id,
                'comment' => $request->comment
            ];

            $repo = new FeedbackRepository(new Feedbacks());
            $data = $repo->create($data);
            if ($data->id) {
                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointmentData = $appointmentRepo->find($request->appointment_id);
                if($appointmentData->auditions->user_id === $request->evaluator) {
                    $slotRepo = new UserSlotsRepository(new UserSlots());
                    $slotData = $slotRepo->findbyparam('slots_id', $request->slot_id)->first();
                    if(isset($slotData)) {
                        $update = $slotData->update([
                            'favorite' => $request->favorite
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
            return response()->json(['data' => 'Feedback not add'], 406);
        }

    }


    public function update(Request $request)
    {
        try {

            $userExists = false;
            $evaluatorExits = false;
            $slotExits = false;

            $data = [
                'evaluation' => $request->evaluation,
                'callback' => $request->callback,
                'work' => $request->work,
                'favorite' => $request->favorite,
                'comment' => $request->comment
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
            return response()->json(['data' => 'Feedback not update'], 422);
        }

    }

    public function list(Request $request)
    {
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
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

    public function finalUserFeedback(Request $request)
    {
        try {
            $repo = new FeedbackRepository(new Feedbacks());
            $repoAppointment = new AppointmentRepository(new Appointments());
            $dataRepo = $repoAppointment->find($request->id);
            $data = $repo->findbyparam('appointment_id', $request->id);

            $dataPre = $data->where('user_id', '=', $this->getUserLogging())->where('evaluator_id','=',$dataRepo->auditions->user_id)->first() ?? new Collection();
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
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }


    public function feedbackDetailsByUser(Request $request)
    {
        try {
            $repoFeedback = new FeedbackRepository(new Feedbacks());

            $feedbacks = $repoFeedback->all()->where('appointment_id', $request->id)
            ->where('evaluator_id',$this->getUserLogging())->where('user_id',$request->user_id);




            if ($feedbacks->count() == 0) {
                throw new \Exception('Data not found');
            }


                $dataResponse = ['data' => $feedbacks];
                $code = 200;


            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }


    function addTalenteToDatabase($performer_id){
        try {
            $hasid = new Hashids('g2g');
            $dateHash = new \DateTime();
            $dataTime =  $dateHash->getTimestamp();
            $repo = new PerformerRepository(new Performers());
            $dataRepo = $repo->findbyparam('director_id',$this->getUserLogging())->get();
            $count = $dataRepo->where('performer_id',$performer_id)->count();
            if($count > 0){
                throw new \Exception("User exists in your database");
            }
            $register = [
                'performer_id' => $performer_id,
                'director_id' => $this->getUserLogging(),
                'uuid' => $hasid->encode($performer_id,$dataTime),
            ];

            $repo->create($register);
            $this->log->info('Talent add');
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());

        }
    }

}
