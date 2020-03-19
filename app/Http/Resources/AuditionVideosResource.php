<?php

namespace App\Http\Resources;

use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditionVideosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userRepo = new UserRepository(new User());
        $dataUser = $userRepo->find($this->performer_id);
        return [
            'id'=>$this->id,
            'name'=>$dataUser->details->first_name." ".$dataUser->details->last_name,
            'url'=>$this->url,
            'thumbnail'=>$this->thumbnail,
        ];
    }
}
