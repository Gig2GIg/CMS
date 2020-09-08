<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditsResource extends JsonResource
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
            'id'=>              $this->id,
            'type' =>           $this->type,
            'rol' =>            $this->rol,
            'name'=>            $this->name,
            'production' =>     $this->production,
            'year' =>           $this->year,
            'end_year' =>       $this->end_year,
            'month' =>          $this->month,
            'user_id'=>         $this->user_id,
        ];
    }
}
