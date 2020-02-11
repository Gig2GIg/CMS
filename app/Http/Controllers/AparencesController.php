<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\UserAparenceRepository;
use App\Http\Requests\AparencesRequest;
use App\Http\Resources\AparenceResource;
use App\Models\UserAparence;

class AparencesController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    /**
     * @param AparencesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AparencesRequest $request)
    {
        try {
            $data = [
                'weight' => $request->weight,
                'height' => $request->height,
                'hair' => $request->hair,
                'eyes' => $request->eyes,
                'race' => $request->race,
                'personal_flare' => $request->personal_flare,
                'user_id' => $this->getUserLogging(),
                'gender_pronouns' => $request->gender_pronouns
            ];
            $repo = new UserAparenceRepository(new UserAparence());
            $repo->create($data);

            $dataResponse = ['data' => 'Aparence created'];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => trans('messages.error')], 500);
            // return response()->json(['error' => 'ERROR'], 500);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function byUser()
    {
        try {
            $repo = new UserAparenceRepository(new UserAparence());
            $data = $repo->findbyparam('user_id', $this->getUserLogging());
            if ($data !== null) {
                $dataResponse = ['data' => new AparenceResource($data)];

                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not Found Data'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            // return response()->json(['data' => 'Not Found Data'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    /**
     * @param AparencesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AparencesRequest $request)
    {
        try {
            $data = [
                'weight' => $request->weight,
                'height' => $request->height,
                'hair' => $request->hair,
                'eyes' => $request->eyes,
                'race' => $request->race,
                'personal_flare' => $request->personal_flare,
                'gender_pronouns' => $request->gender_pronouns
            ];
            $repo = new UserAparenceRepository(new UserAparence());
            $dataManager = $repo->find(request('id'));
            $result = $dataManager->update($data);
            if ($result) {
                $dataResponse = ['data' => 'Aparence updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Aparence not updated'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                $code = 404;
            } else {
                $code = 406;
            }
            // return response()->json(['data' => 'Error not Processable'], $code);
            return response()->json(['data' => trans('messages.error_not_processable')], $code);
        }
    }
}
