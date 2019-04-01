<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAuditionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dataProduction = explode(",",$this->auditions->production);
        $url_media=$this->auditions->resources
            ->where('type','image')
            ->where('resource_type','App\Models\Auditions')
            ->pluck('url');

        return [
            'id' => $this->id,
            'title' => $this->auditions->title,
           'date' => $this->auditions->date,
            'union' => $this->auditions->union,
           'contract' => $this->auditions->contract,
            'production' => $dataProduction,
            'media' =>$url_media[0] ?? null,
            'roles_count'=>count($this->auditions->roles),


        ];
    }
}
