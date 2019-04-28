<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Auditions;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AuditionResponse extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $count = count($this->roles);
        $dataProduction = explode(',',$this->production);
        $url_media=$this->resources
            ->where('type','cover')
            ->where('resource_type','App\Models\Auditions')
            ->pluck('url');
        $userDataRepo = new UserDetailsRepository(new UserDetails());
        $data = $userDataRepo->findbyparam('user_id',$this->user_id);
        return [
            'id' => $this->id,
            'id_user'=>$this->user_id,
            'agency'=>$data->agency_name ?? null,
            "title" => $this->title,
            "date" => $this->date,
            'create'=>$this->created_at,
            "time" => $this->time,
            "location" => json_decode($this->location),
            "description" => $this->description,
            "url" => $this->url,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => $dataProduction,
            "status" => $this->status,
            "user_id" => $this->user_id,
            "cover" => $url_media[0] ??null,
            "number_roles" => $count,

        ];
    }
}
