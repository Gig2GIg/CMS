<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\PostsRepository;
use App\Http\Repositories\PostTopicsRepository;
use App\Http\Requests\PostsRequest;
use App\Http\Resources\PostsResource;
use App\Http\Resources\PostsTopicsWithPostResource;
use App\Models\Posts;
use App\Models\PostTopics;
use Illuminate\Http\Request;

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
                'title' => $request->title,
                'body' => $request->body,
                'url_media' => $request->url_media,
                'type' => $request->type,
                'search_to' => $request->search_to,
                'user_id' => $this->getUserLogging(),
            ];

            $repoPost = new PostsRepository(new Posts());
            $post = $repoPost->create($data);

            if (!is_null($post)) {
                $repoPostTopic = new PostTopicsRepository(new PostTopics());

                if (isset($request['topic_ids'])) {
                    foreach ($request['topic_ids'] as $topic) {
                        $repoPostTopic->create(['post_id' => $post->id, 'topic_id' => $topic['id']]);
                    }
                }
            }

            $dataResponse = [
                'message' => 'Post created',
                'data' => $post,
            ];

            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => trans('messages.error')], 422);
            // return response()->json(['error' => 'ERROR'], 422);
        }
    }

    public function update(Request $request)
    {
        try {
            $data = [
                'title' => $request->title,
                'body' => $request->body,
                'url_media' => $request->url_media,
                'type' => $request->type,
                'search_to' => $request->search_to,
            ];

            $repoPost = new PostsRepository(new Posts());
            $post = $repoPost->find($request->id);

            if ($post->update(array_filter($data))) {                
                if (isset($request['topic_ids'])) {
                    //Post Topics Delete previous ones
                    $collection = PostTopics::where('post_id', $request->id)->get(['id']);
                    PostTopics::destroy($collection->toArray());

                    //Add new topics
                    $repoPostTopic = new PostTopicsRepository(new PostTopics());
                    foreach ($request['topic_ids'] as $topic) {
                        $repoPostTopic->create(['post_id' => $request->id, 'topic_id' => $topic['id']]);
                    }
                }

                $dataResponse = ['data' => 'Post update'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Post not update'];
                $code = 422;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function delete(Request $request)
    {
        try {
            $post = Posts::findOrfail($request->id);

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

    function list(Request $request) {
        try {
            $repoPost = new PostsRepository(new Posts());
            $posts = $repoPost->all();

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsResource::collection($posts)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function searcByTopics(Request $request)
    {
        try {

            $postTopics = PostTopics::whereIn('topic_id', $request->topics_ids)->get();

            $posts = $postTopics->map(function ($postTopics) {
                return $postTopics;
            });

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsTopicsWithPostResource::collection($posts)];
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

    public function searchPostToPerformance(Request $request)
    {
        try {
            $postTopics = PostTopics::whereIn('topic_id', $request->topics_ids)->get();

            $posts = $postTopics->map(function ($postTopics) {
                return $postTopics;
            })->flatten()->unique();

            $data = PostsTopicsWithPostResource::collection($posts);
            $response = collect($data)->filter()->all();

            if (count($posts) > 0) {
                $dataResponse = ['data' => $response];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function listForum(Request $request)
    {
        try {
            $repoPost = new PostsRepository(new Posts());
            $posts = $repoPost->all()->where('type', 'forum')->sortByDesc('created_at');

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsResource::collection($posts)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function listBlog(Request $request)
    {
        try {
            $repoPost = new PostsRepository(new Posts());
            $posts = $repoPost->all()->where('type', 'blog')->sortByDesc('created_at');

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsResource::collection($posts)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function listPostToPerformance(Request $request)
    {
        try {
            $repoPost = new PostsRepository(new Posts());
            $posts = $repoPost->all()->where('type', 'blog');

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsResource::collection($posts->where('search_to', '!=', 'director'))];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function listPostToDirector(Request $request)
    {
        try {
            $repoPost = new PostsRepository(new Posts());
            $posts = $repoPost->all()->where('type', 'blog');

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsResource::collection($posts->where('search_to', '!=', 'performance'))];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function search_post_by_title(Request $request)
    {
        if (!is_null($request->value)) {
            $postRepo = new PostsRepository(new Posts());

            $result = $postRepo->search_by_title($request->value);
            $post = $result->where('type', 'blog');

            $count = count($post);

            if ($count > 0) {
                $responseData = PostsResource::collection($post);
                return response()->json(['data' => $responseData], 200);
            } else {
                // return response()->json(['data' => "Not found Data"], 404);
                return response()->json(['data' => trans('messages.data_not_found')], 404);
            }
        } else {
            // return response()->json(['data' => "Not found Data"], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function search_forum_by_title(Request $request)
    {
        if (!is_null($request->value)) {
            $postRepo = new PostsRepository(new Posts());

            $result = $postRepo->search_by_title($request->value);
            $post = $result->where('type', 'forum');

            $count = count($post);

            if ($count > 0) {
                $responseData = PostsResource::collection($post);
                return response()->json(['data' => $responseData], 200);
            } else {
                // return response()->json(['data' => "Not found Data"], 404);
                return response()->json(['data' => trans('messages.data_not_found')], 404);
            }
        } else {
            // return response()->json(['data' => "Not found Data"], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function sort_post_by_param_to_director(Request $request)
    {
        try {

            if ($request->order_by === 'desc') {
                $posts = Posts::where('search_to', '!=', 'performance')->get()->sortByDesc('created_at');
            }

            if ($request->order_by === 'asc') {

                $posts = Posts::where('search_to', '!=', 'performance')->get()->sortBy('created_at');
            }

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsResource::collection($posts)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

    public function sort_post_by_param_to_performance(Request $request)
    {
        try {

            if ($request->order_by === 'desc') {
                $posts = Posts::where('search_to', '!=', 'director')->get()->sortByDesc('created_at');
            }

            if ($request->order_by === 'asc') {

                $posts = Posts::where('search_to', '!=', 'director')->get()->sortBy('created_at');
            }

            if (count($posts) > 0) {
                $dataResponse = ['data' => PostsResource::collection($posts)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);

        }
    }
}
