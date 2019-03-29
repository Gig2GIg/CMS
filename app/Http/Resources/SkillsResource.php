<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class SkillsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if(isset($this->skills)){
            Log::info($this->skills);
            return [
                'id'=>$this->id,
                'name'=>$this->skills->name,
            ];
        }else{
            return [
                'id'=>$this->id,
                'name'=>$this->name,
            ];
        }


    }
}
