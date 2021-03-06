<?php

namespace App\Http\Resources;

use App\Http\Repositories\SlotsRepository;
use App\Models\Roles;
use App\Models\Slots;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuditionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $dataHour = null;
        $dataProduction = explode(",", $this->auditions->production);
        $url_media = $this->auditions->resources
            ->where('type', 'cover')
            ->where('resource_type', 'App\Models\Auditions')
            ->pluck('url');
        $rolanme = Roles::where('id','=',$this->rol_id)->get()->pluck('name');
        $slot = $this->slot_id;
        if($slot != null){
            $repoSlot = new SlotsRepository(new Slots());
            $dataSlots = $repoSlot->find($slot);
            $dataHour = $dataSlots->time;
        }
        return [
            'id' => $this->id,
            'auditions_id'=>$this->auditions_id,
            'rol'=> $this->rol_id,
            'rol_name'=>$rolanme[0] ?? null,
            'id_user' => $this->auditions->user_id,
            'title' => $this->auditions->title,
            'date' => $this->auditions->date,
            'hour' => $dataHour,
            'union' => $this->auditions->union,
            'contract' => $this->auditions->contract,
            'production' => $dataProduction,
            'media' => $url_media[0] ?? null,
            'number_roles' => count($this->auditions->roles),


        ];
    }
}
