<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-27
 * Time: 11:48
 */

namespace App\Http\Resources;


use App\Models\Resources;
use App\Models\Roles;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditionResourceFind extends JsonResource
{
    public function toArray($request)
    {
$roles = new Roles();
$countRoles = $roles->where('auditions_id',$this->id)->count();
$media = new Resources();
        $url_media=$media
            ->where('type','cover')
            ->where('resource_type','App\Models\Auditions')
            ->pluck('url');
        return [
            'id' => $this->id,
            'title' => $this->title,
            "date" => $this->date,
            'create'=>$this->created_at,
            "time" => $this->time,
            "location" => $this->location,
            "description" => $this->description,
            'url' => $this->url,
            'personal_information'=>$this->personal_information,
            'additional_info'=>$this->additional_info,
            'phone'=>$this->phone,
            'email'=>$this->email,
            'other_info'=>$this->other_info,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => explode(',',$this->production),
            "status" => $this->status,
            "user_id" => $this->user_id,
            "cover" => $url_media[0] ??null,
            "number_roles" => $countRoles,
        ];
    }

}
