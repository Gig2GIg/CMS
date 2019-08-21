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
        $user = $userRepo->find($this->user_id);
        $avatar = $user->image->url;
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'avatar' => $avatar,
            'name' => $user->details->first_name,
            'time_ago' => $this->created_at->diffForHumans()
        ];
    }
}
