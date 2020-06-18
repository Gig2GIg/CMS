<?php

namespace App\Http\Resources;

use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Models\Roles;
use App\Models\UserDetails;
use Illuminate\Http\Resources\Json\JsonResource;

class FinalCastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $repoUserDet = new UserDetailsRepository(new UserDetails());
        $repoRol = new RolesRepository(new Roles());
        $dataDetUser = $repoUserDet->findbyparam('user_id',$this->performer_id)->first();
        $dataRol = $repoRol->find($this->rol_id);
        $fname = $dataDetUser->first_name ?? '';
        $lname=$dataDetUser->last_name ?? '';
       return [
           'id'=>$this->id,
           'user_id'=>$this->performer_id,
           'rol_id'=>$this->rol_id,
           'name'=>$fname.' '.$lname,
           'rol_name'=>$dataRol->name,
       ];
    }
}
