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
        // \DB::enableQueryLog();
        $repo = new UserRepository(new User());
        $data = $repo->find($this->user_id);
        $repoPerfomer = new PerformerRepository(new Performers());
        $dataUUID = $repoPerfomer->findByMultiVals('director_id', $request->allIdsToInclude->unique()->values())->get()->where('performer_id',$this->user_id)->first();
       
        $imageData = $data->image()->where('type','=','cover')->get();
        $img = $imageData->pluck('url')[0] ?? NULL;
        $thumb = $imageData->pluck('thumbnail')[0] ?? NULL;

        return [
            'share_code'=>$dataUUID->uuid,
            'image'=>$img,
            'thumbnail'=>$thumb,
            'details'=>$data->details,
            'appearance'=>$data->aparence,
            'education'=>$data->educations,
            'credits'=>$data->credits,
            'unions'=>$data->memberunions,
            'calendar'=>$data->calendars,
        ];
    }
}
