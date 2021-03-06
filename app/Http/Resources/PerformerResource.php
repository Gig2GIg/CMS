<?php

namespace App\Http\Resources;

use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $repo = new UserRepository(new User());
        $data = $repo->find($this->performer_id);
        return [
            'share_code'=>$this->uuid,
            'image'=>$data->image()->where('type','=','cover')->get()->pluck('url')[0],
            'details'=>$data->details,
            'appearance'=>$data->aparence,
            'education'=>$data->educations,
            'credits'=>$data->credits,
            'calendar'=>$data->calendars,
        ];
    }
}
