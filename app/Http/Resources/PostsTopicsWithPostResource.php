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
        $post = $postRepo->find($this->post_id);

        $userRepo = new UserRepository(new User());
        
        if($post->admin_id == null && $post->user_id != null){
            $user = $userRepo->find($post->user_id);
            $name = $user->details->first_name;
            $avatar = $user->image->url;
            $is_admin = 0;
        }else{
            $name = 'Gig2Gig Team';
            $avatar = "";
            $is_admin = 1;
        }

        //if ($post->search_to != 'director'){
            return [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'type' => $post->type,
                'url_media' => $post->url_media,
                'avatar' => $avatar,
                'url_media' => $post->url_media,
                'name' => $name,
                'time_ago' => $post->created_at->diffForHumans(),
                'search_to' => $post->search_to,
                'is_admin' => $is_admin
            ];
        //}
    }
}
