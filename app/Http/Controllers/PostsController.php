<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;

use App\Models\Posts;
use Illuminate\Http\Request;

use App\Http\Requests\AuditionEditRequest;

use App\Http\Repositories\PostsRepository;
use App\Http\Repositories\FeedbackRepository;

use App\Http\Resources\PostsResource;

class PostsController extends Controller
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
                'title'=> $request->title,
                'body'=>$request->body
            ];

            $repoPost = new PostsRepository(new Posts());
            $tag = $repoPost->create($data);

            $dataResponse = [
                'message' =>'Post created',
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
            $repoPost = new PostsRepository(new Posts());
            $post = $repoPost->find($request->id);

            if ($post->delete()) {
                $dataResponse = ['data' => 'Post removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Post not removed'];
                $code = 404;
            }
      
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }


    public function list(Request $request)
    {
        try {
            $repoPost = new PostsRepository(new Posts());
            $posts = $repoPost->all();

            if (count($posts) > 0 ) {
                $dataResponse = ['data' => PostsResource::collection($posts)];
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
