<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AuditionFullResponse extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this->contributors->each(function ($item) {
            $user = new UserRepository(new User());
            $userData = $user->find($item->user_id);
            $userData->push($userData->details);
            $item['contributor_info'] = $userData;
        });
        $userDataRepo = new UserDetailsRepository(new UserDetails());
        $dataUserDet = $userDataRepo->findbyparam('user_id',$this->user_id);
        $this->roles->each(function($item){
            $item->image;
        });
        $appoinment = $this->appointment;
        $dataProduction = explode(',',$this->production);
        if($this->status){
            $appoinment = $this->appointment->where('status',true)->first();
        }else{
            $appoinment = $this->appointment->first();
        }
        $slotsData = new SlotsRepository(new Slots());
        $slots = $slotsData->findbyparam('appointment_id',$appoinment->id)->get();

        $appoinmentResponse =  ['general' => $this->appointment, 'slots' => $slots];
        return [
            'id' => $this->id,
            'title' => $this->title,
            'date' => $appoinment->date ?? null,
            'time' => $appoinment->time ?? null,
            'create'=>$this->created_at,
            'location' => json_decode($appoinment->location) ?? null,
            'description' => $this->description,
            'url' => $this->url,
            'personal_information'=>$this->personal_information,
            'additional_info'=>$this->additional_info,
            'phone'=>$this->phone,
            'email'=>$this->email,
            'other_info'=>$this->other_info,
            'dates'=>$this->datesall,
            'union' => $this->union,
            'contract' => $this->contract,
            'production' => $dataProduction,
            'cover'=>$this->resources()->where('resource_type','=','App\Models\Auditions')->where('type','=','cover')->get()[0]['url'] ?? null,
            'id_cover'=>$this->resources()->where('resource_type','=','App\Models\Auditions')->where('type','=','cover')->get()[0]['id'] ?? null,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'director' => $this->user->load('details'),
            'agency'=>$dataUserDet->agency_name ?? null,
            'roles' => $this->roles,
            'media' => $this->resources()->where('resource_type','=','App\Models\Auditions')->where('type','!=','cover')->get(),
            'apointment' => $appoinmentResponse,
            'contributors' => $this->contributors
        ];
    }
}
