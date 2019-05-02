<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Resources\FeedbackResource;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\UserAuditions;
use Illuminate\Http\Request;
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
            $data = [
                'auditions_id' => $request->auditions,
                'user_id' => $request->user, //id usuario que recibe evaluacion
                'evaluator_id' => $request->evaluator,//id de usuario que da feecback,
                'evaluation' => $request->evaluation,
                'callback' => $request->callback,
                'work' => $request->work,
                'favorite' => $request->favorite,
            ];
            $repo = new FeedbackRepository(new Feedbacks());
            $data = $repo->create($data);
            if ($data->id) {

                $dataResponse = ['data' => 'Feedback add'];
                $code = 201;

            } else {
                $dataResponse = ['data' => 'Feedback not add'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Feedback not add'], 406);
        }

    }

    public function list(Request $request)
    {
        try {
            $repo = new FeedbackRepository(new Feedbacks());
            $data = $repo->findbyparam('auditions_id', $request->audition);
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
            $repoAudi = new AuditionRepository(new Auditions());

            $dataAudi = $repoAudi->find($request->id);

            $data = $repo->findbyparam('auditions_id', $request->id);

            $dataPre = $data->where('user_id', '=', $this->getUserLogging())->where('evaluator_id','=',$dataAudi->user_id)->first() ?? new Collection();
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

}
