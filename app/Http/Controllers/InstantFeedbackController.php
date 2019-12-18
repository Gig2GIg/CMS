<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Models\InstantFeedback;
use App\Http\Repositories\InstantFeedbackRepository;
use Illuminate\Http\Request;
use App\Models\Auditions;

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
                'user_id' => $request->user, //id usuario que recibe evaluacion
                'evaluator_id' => $request->evaluator, //id de usuario que da feecback,
                'comment' => $request->comment
            ];

            $repo = new InstantFeedbackRepository(new InstantFeedback());
            $data = $repo->create($data);
            if ($data->id) {

                $dataResponse = ['data' => trans('messages.feedback_save_success'), 'feedback_id' => $data->id];
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


    public function search_with_upcoming_audition(Request $request)
    {
        if (!is_null($request->value)) {
            $auditionRepo = new Auditions();
            $data = $auditionRepo
                // ->select('id')
                ->where('title', 'like', "%{$request->value}%")
                ->where('status', 1)
                ->get();
            return response()->json(['data' => $data], 200);
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
     
            if ($feedbacks == NULL) {
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

            $feedbacks = $repoFeedback->all()->where('appointment_id', $request->id)
              ->where('user_id', '=', $request->user_id)->first();
     
            if ($feedbacks == NULL) {
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


    
}
