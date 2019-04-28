<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditionsDetResponse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id,
            'audition_id' => $this->auditions_id,
            'title' => $this->auditions->title,
            'date' => $this->auditions->date,
            'time' => $this->auditions->time,
            'create'=>$this->created_at,
        ];
    }
}
