<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,
            "title" => $this->title,
            "date" => $this->date,
            "time" => $this->time,
            "location" => $this->location,
            "description" => $this->description,
            "url" => $this->url,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => $this->production,
            "status" => $this->status,
            "user_id" => $this->user_id,
            "roles" => $this->roles,
            "media"=>$this->resources,
            "apointment" => $this->appointment,
            "contributors"=>$this->contributors()->with('userdetails')->get(),
        ];
    }
}
