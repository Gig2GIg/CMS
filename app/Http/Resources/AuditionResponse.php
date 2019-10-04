<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use DemeterChain\A;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AuditionResponse extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $count = count($this->roles);
        $dataProduction = explode(',',$this->production);
        $url_media=$this->resources
            ->where('type','cover')
            ->where('resource_type','App\Models\Auditions')
            ->pluck('url');
        $userDataRepo = new UserDetailsRepository(new UserDetails());
        $data = $userDataRepo->findbyparam('user_id',$this->user_id);
        $repoA = new AppointmentRepository(new Appointments());

        if($this->status!=2){

            $appointmentData = $repoA->findbyparam('auditions_id',$this->id)->where('status',true)->first();
        }else{

            $appointmentData = $repoA->findbyparam('auditions_id',$this->id)->sortBy('created_at')->first();
        }
        Log::info($appointmentData->toArray());
        $location = isset($appointmentData->location) ?$appointmentData->location:'{}';
        return [
            'id' => $this->id,
            'id_user'=>$this->user_id,
            'agency'=>$data->agency_name ?? null,
            "title" => $this->title,
            "date" => $appointmentData->date ?? null,
            'create'=>$this->created_at,
            "time" => $appointmentData->time ?? null,
            "location" => json_decode($location),
            "description" => $this->description,
            "url" => $this->url,
            'personal_information'=>$this->personal_information,
            'phone'=>$this->phone,
            'email'=>$this->email,
            'other_info'=>$this->other_info,
            'additional_info'=>$this->additional_info,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => $dataProduction,
            "status" => $this->status,
            "user_id" => $this->user_id,
            "cover" => $url_media[0] ??null,
            "number_roles" => $count,

        ];
    }
}
