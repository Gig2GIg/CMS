<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Auditions;
use App\Models\Slots;
use App\Models\User;
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
            ->where('type','image')
            ->where('resource_type','App\Models\Auditions')
            ->pluck('url');
        return [
            'id' => $this->id,
            "title" => $this->title,
            "date" => $this->date,
            "time" => $this->time,
            "location" => explode(',',$this->location),
            "description" => $this->description,
            "url" => $this->url,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => $dataProduction,
            "status" => $this->status,
            "user_id" => $this->user_id,
            "media" => $url_media[0] ??null,
            "number_roles" => $count,

        ];
    }
}
