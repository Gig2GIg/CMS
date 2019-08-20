<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;


use Illuminate\Http\Request;
use App\Http\Resources\TopicsResource;

use App\Models\Topics;

use App\Http\Repositories\TopicsRepository;

use App\Http\Resources\TagsResource;

class TopicsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function list(Request $request)
    {
        try {
            $feedbackRepo = new TopicsRepository(new Topics());
            $data = $feedbackRepo->all();

            if (count($data) > 0 ) {
                $dataResponse = ['data' =>   TopicsResource::collection($data)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }
      
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }
}
