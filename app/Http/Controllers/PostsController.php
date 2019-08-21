<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;

use App\Models\Posts;
use App\Models\PostTopics;

use Illuminate\Http\Request;
use App\Http\Requests\PostsRequest;

use App\Http\Repositories\PostsRepository;
use App\Http\Repositories\PostTopicsRepository;

use App\Http\Resources\PostsResource;

class PostsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(PostsRequest $request)
    {
        try {
            $data = [
                'title'=> $request->title,
                'body'=>$request->body,
                'url_media' =>  $request->url_media,
                'type' => $request->type,
                'search_to' =>  $request->search_to,
                'user_id' => $this->getUserLogging()
            ];

            $repoPost = new PostsRepository(new Posts());
            $post = $repoPost->create($data);

            if (! is_null($post))
            {
                $repoPostTopic = new PostTopicsRepository(new PostTopics());
           
                if (isset($request['topic_ids'])) {
                    foreach ($request['topic_ids'] as $topic) {
                        $repoPostTopic->create(['post_id' =>  $post->id, 'topic_id' => $topic['id']]);
                    }
                }
            }
            
            $dataResponse = [
                'message' =>'Post created',
                'data' => $post
            ];
            
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }
    }
    
    public function update(Request $request)
    {
        try {
            $data = [
                'title'=> $request->title,
                'body'=>$request->body,
                'url_media' =>  $request->url_media,
                'type' => $request->type,
                'search_to' =>  $request->search_to
            ];

            $repoPost = new PostsRepository(new Posts());
            $post = $repoPost->find($request->id);

            if ($post->update(array_filter($data))) {
                $dataResponse = ['data' => 'Post update'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Post not update'];
                $code = 422;
            }
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

    

    public function listForum(Request $request)
    {
        try {
            $repoPost = new PostsRepository(new Posts());
            $posts = $repoPost->all()->where('type', 'forum');

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



    public function search_post_by_title(Request $request)
    {
        if (! is_null($request->value)) {
            $postRepo = new PostsRepository(new Posts());
           
            $result = $postRepo->search_by_title($request->value);
            $post = $result->where('type', 'blog');
       
            $count = count($post);

            if ($count > 0) {
                $responseData = PostsResource::collection($post);
                return response()->json(['data' => $responseData], 200);

            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }  

        } else {
            return response()->json(['data' => "Not found Data"], 404);
        }
       
    }


    public function search_forum_by_title(Request $request)
    {
        if (! is_null($request->value)) {
            $postRepo = new PostsRepository(new Posts());
           
            $result = $postRepo->search_by_title($request->value);
            $post = $result->where('type', 'forum');
       
            $count = count($post);

            if ($count > 0) {
                $responseData = PostsResource::collection($post);
                return response()->json(['data' => $responseData], 200);

            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }  

        } else {
            return response()->json(['data' => "Not found Data"], 404);
        }
       
    }
}
