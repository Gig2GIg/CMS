<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAuditionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $dataProduction = explode(",", $this->auditions->production);
        $url_media = $this->auditions->resources
            ->where('type', 'image')
            ->where('resource_type', 'App\Models\Auditions')
            ->pluck('url');

        return [
            'id' => $this->id,
            'id_user' => $this->auditions->user_id,
            'title' => $this->auditions->title,
            'date' => $this->auditions->date,
            'hour' => $this->auditions->time,
            'union' => $this->auditions->union,
            'contract' => $this->auditions->contract,
            'production' => $dataProduction,
            'media' => $url_media[0] ?? null,
            'number_roles' => count($this->auditions->roles),


        ];
    }
}
