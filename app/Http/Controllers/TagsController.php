<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;

use App\Models\Tags;
use App\Models\User;
use App\Models\Auditions;

use Illuminate\Http\Request;
use App\Http\Repositories\TagsRepository;

use App\Http\Resources\TagsResource;
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
                'audition_id'=>$request->audition_id,
                'user_id'=>$request->user_id
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
            

            if ($tag->delete()) {
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




    public function updateFromArray(Request $request)
    {
        try {
            $repoTag = new TagsRepository(new Tags());
           
            if (!! $request->tags){
                $tags = $request->tags;
                for ($i = 0; $i <= count($tags) -1; $i++) {
                
                    $repoTag->find($tags[$i]['id']);

                    if (! is_null($tag) ){
                        $repoTag->update([
                                'url' => $tags[$i]['title']
                        ]);
                    }

                    if ( is_null($tag[$i]['id']) ){
                        $repoTag->create([
                                'title' => $tags[$i]['title'],
                                'audition_id' => $tags[$i]['audition_id'],
                                'user_id' => $tasg[$i]['user_id']
                        ]);
                    }
                }
            }

            $dataResponse = ['data' => 'Tags updates'];
            $code = 200;
           
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }

    public function listByUser(Request $request)
    {
        try {
           
            $tags = Tags::where('audition_id', $request->id)->get();

            $userTags = $tags->where('user_id', $request->user_id);

            if (! is_null($userTags )) {
                $dataResponse = ['data' =>   TagsResource::collection($userTags)];
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

