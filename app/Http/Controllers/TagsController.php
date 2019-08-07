<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;

use App\Models\Tags;
use Illuminate\Http\Request;

use App\Http\Repositories\TagsRepository;

class TagsController extends Controller
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
                'title'=>$request->title,
                'feedback_id'=>$request->feedback_id
            ];

            $repoTag = new TagsRepository(new Tags());
            $tag = $repoTag->create($data);

            $dataResponse = [
                'message' =>'Tag created',
                'data' => $tag
            ];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }


    public function delete(Request $request)
    {
        try {
            $repoTag = new TagsRepository(new Tags());
            $tag = $repoTag->find($request->id);

            if ($tag) {
                $dataResponse = ['data' => 'Tag removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Tag not removed'];
                $code = 404;
            }
      
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }
}
