<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\User;
use App\Http\Repositories\UserRepository;


class PostsResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)

    {
        $userRepo = new UserRepository(new User());

        if($this->admin_id == null && $this->user_id != null){
            $user = $userRepo->find($this->user_id);
            $name = $user->details->first_name;
            $avatar = $user->image ? $user->image->url : NULL;
            $is_admin = 0;
        }else{
            $name = 'Gig2Gig Team';
            $avatar = "";
            $is_admin = 1;
        }
        
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'avatar' => $avatar ?? "",
            'url_media' => $this->url_media,
            'name' => $name ?? "",
            'time_ago' => $this->created_at->diffForHumans(),
            'search_to' => $this->search_to,
            'type' => $this->type,
            'created_at' => isset($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : '',
            'topic_id' => isset($this->post_topics[0]->topic_id) ? $this->post_topics[0]->topic_id : "",
            'is_admin' => $is_admin
        ];
    }
}
