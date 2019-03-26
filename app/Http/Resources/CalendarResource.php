<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CalendarResource extends JsonResource
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
            'production_type' => $this->production_type,
            'project_name' => $this->project_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            "user_id" => $this->user_id,
        ];
    }
}
