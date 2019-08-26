<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\Posts;
use App\Models\User;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\PostsRepository;

class PostsTopicsWithPostResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $postRepo = new PostsRepository(new Posts());
        $post = $postRepo->find($this->topic_id);

        $userRepo = new UserRepository(new User());
        $user = $userRepo->find($post->user_id);
        $avatar = $user->image->url;

        if ($post->type != 'director'){
            return [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'type' => $post->type,
                'url_media' => $post->url_media,
                'avatar' => $avatar,
                'url_media' => $this->url_media,
                'name' => $user->details->first_name,
                'time_ago' => $this->created_at->diffForHumans()
            ];
        }   
    }
}
