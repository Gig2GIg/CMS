<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;

use App\Models\Comments;
use App\Models\Posts;

use Illuminate\Http\Request;

use App\Http\Requests\CommentsRequest;

use App\Http\Repositories\CommentsRepository;
use App\Http\Repositories\PostsRepository;

use App\Http\Resources\TagsResource;
use App\Http\Resources\CommentsResource;
use App\Http\Resources\PostsResource;
use App\Http\Resources\TopicsResource;
use App\Http\Resources\PostsTopicsResource;

class CommentsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(CommentsRequest $request)
    {
        try {

            $data = [
                'body' => $request->body,
                'post_id' => $request->id,
                'user_id' => $this->getUserLogging()
            ];

            $repoComment = new CommentsRepository(new Comments());

            $comment = $repoComment->create($data);

            $dataResponse = [
                'message' => 'comment created',
                'data' => $comment
            ];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => trans('messages.error')], 422);
            // return response()->json(['error' => 'ERROR'], 422);
        }
    }


    public function delete(Request $request)
    {
        try {
            $repoPost = new CommentsRepository(new Comments());
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
            return response()->json(['error' => trans('messages.error')], 422);
            // return response()->json(['error' => 'ERROR'], 422);
        }
    }

    public function list(Request $request)
    {
        try {
            $postRepo = new PostsRepository(new Posts());
            $post = $postRepo->find($request->id);

            if (!is_null($post)) {
                $topics = PostsTopicsResource::collection($post->post_topics);
                $post_resources = new PostsResource($post);
                $dataResponse = ['data' => [
                    'post' => ['post_data' => $post_resources, 'topics' => $topics],
                    'comments' => CommentsResource::collection($post->comments->sortByDesc('created_at'))
                ]];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => trans('messages.error')], 422);
            // return response()->json(['error' => 'ERROR'], 422);
        }
    }
}
