<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserRepository;
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
        return [
            'id' => $this->id,
            "title" => $this->title,
            "date" => $this->date,
            "time" => $this->time,
            "location" => $this->location,
            "description" => $this->description,
            "url" => $this->url,
            "dates"=>$this->datesall,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => $this->production,
            "status" => $this->status,
            "user_id" => $this->user_id,
            "number_roles" => $count,

        ];
    }
}
