<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NoficationsResource extends JsonResource
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
            'title' => $this->title,
            'status' => $this->status,
            'code' => $this->code,
            'custom_data' => $this->custom_data,
            'time_ago' => $this->created_at->diffForHumans()
        ];
    }
}
