<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManagerResource extends JsonResource
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
            'name'=>            $this->name,
            'company'=>         $this->company,
            'type'=>            $this->type,
            'notifications'=>   $this->notifications,
            'email'=>           $this->email
        ];
    }
}
