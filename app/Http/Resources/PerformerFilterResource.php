<?php

namespace App\Http\Resources;

use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Performers;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PerformerFilterResource extends JsonResource
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
        $data = $repo->find($this->user_id);
        $repoPerfomer = new PerformerRepository(new Performers());
            $dataUUID = $repoPerfomer->findbyparam('director_id', Auth::user()->getAuthIdentifier())->get()->where('performer_id',$this->user_id)->first();

        return [
            'share_code'=>$dataUUID->uuid,
            'image'=>$data->image()->where('type','=','cover')->get()->pluck('url')[0],
            'details'=>$data->details,
            'appearance'=>$data->aparence,
            'education'=>$data->educations,
            'credits'=>$data->credits,
            'unions'=>$data->memberunions,
            'calendar'=>$data->calendars,
        ];
    }
}
