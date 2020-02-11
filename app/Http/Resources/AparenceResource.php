<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AparenceResource extends JsonResource
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
            'id' => $this->id,
            'height' => $this->height,
            'weight' => $this->weight,
            'hair' => $this->hair,
            'eyes' => $this->eyes,
            'race' => $this->race,
            'personal_flare' => $this->personal_flare,
            'gender_pronouns' => $this->gender_pronouns
        ];
    }
}
