<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\Posts;
use App\Models\Topics;

use App\Http\Repositories\TopicsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;

use App\Http\Repositories\PostsRepository;

class CommentsResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $userDetailRepo = new UserDetailsRepository(new UserDetails());
        $userDataDetail = $userDetailRepo->findbyparam('user_id',$this->user_id);
        
        $userRepo = new UserRepository(new User());
        $userData = $userRepo->find($this->user_id);
        
    
    
        return [
            'id' => $this->id,
            'body' => $this->body,
            'time_ago' => $this->created_at->diffForHumans(),
            'name' => $userDataDetail->first_name,
            'avatar' => $userData->image->url
        ];
    }
}
