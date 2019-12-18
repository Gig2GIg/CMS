<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EducationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'school'=>$this->school,
            'degree'=>$this->degree,
            'instructor'=>$this->instructor,
            'location'=>$this->location,
            'year'=>$this->year,
            'user_id'=>$this->user_id
        ];
    }
}
