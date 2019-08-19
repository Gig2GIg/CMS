<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Resources\TopicsResource;

use App\Models\Topics;

use App\Http\Repositories\TopicsRepository;

use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

    public function getAll()
    {
        $data = new TopicsRepository(new Topics);
        $count = count($data->all());
        return TopicsResource::collection($data->all());
    }

    public function store(Request $request)
    {
        try {
            if ($request->json())
                {
                    $data = [
                        'title' => $request->title,
                        'status' => $request->status
                    ];

                    $topicRepo = new TopicsRepository(new Topics);

                    $result = $topicRepo->create($data);

                    return response()->json(['data' => new TopicsResource($result)], 201);
                }else {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
        } catch (\Exception $e) {
            return response()->json(['data' => "No created Data"], 422);
        }
    }

    public function update(Request $request)
    {
        try {
            if ($request->json()) {

                $data = [
                    'title' => $request->title
                ];

                $topicRepo = new TopicsRepository(new Topics);

                $topic =  $topicRepo->find($request->id);
                $topic->update($data);

              return response()->json(['data' => 'Topic Updated'], 204);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }


    public function delete(Request $request)
    {
        try {
            $topicRepo = new TopicsRepository(new Topics());
            $topic = $topicRepo->find($request->id);
            $topic->delete();

            return response()->json(['data' => 'Topic Product  deleted'], 204);
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => "Unprocesable"], 422);
        }
    }

}
