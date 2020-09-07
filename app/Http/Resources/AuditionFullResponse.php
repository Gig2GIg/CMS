<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Appointments;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserAuditions;
use App\Models\OnlineMediaAudition;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        $user = new UserRepository(new User());
        $uData = $user->find($this->user_id);
        if($uData){
            $admin_id = $uData->invited_by;
        }else{
            $admin_id = NULL;
        }

        $this->contributors->each(function ($item) use($user) {
            $userData = $user->find($item->user_id);
            $userData->push($userData->details);
            $item['contributor_info'] = $userData;
        });
        $userDataRepo = new UserDetailsRepository(new UserDetails());
        $dataUserDet = $userDataRepo->findbyparam('user_id',$this->user_id);
        $this->roles->each(function($item){
            $item->image;
        });
        $dataProduction = explode(',',$this->production);
        
        $appointment = $this->appointment()->where('status', 1)->first();
        if(!$appointment){
            $appointment = $this->appointment()->latest()->first();
            if($appointment){
                $submissionsCount = OnlineMediaAudition::where('appointment_id', $appointment->id)->groupBy('performer_id')->get()->count();
            }else{
                $submissionsCount = 0;
            }
        }else{
            $submissionsCount = UserAuditions::where('type', 1)->where('appointment_id', $appointment->id)->count();
        }

        $slotsData = new SlotsRepository(new Slots());
        $slots = $slotsData->findbyparam('appointment_id',$appointment["id"])->get();
//        $location = isset($appointmentData->location) ? $appointment->location:'';
        $appoinmentResponse =  ['general' => $appointment, 'slots' => $slots];
        
        $coverData = $this->resources()->where('resource_type','=','App\Models\Auditions')
                    ->where('type','=','cover')
                    ->get();
        
        $coverUrl = $coverData[0]['url'] ?? null;
        $coverThumb = $coverData[0]['thumbnail'] ?? null;

        $return = [
            'id' => $this->id,
            'appointment_id'=>$appointment["id"],
            'title' => $this->title,
            'date' => $appointment->date ?? null,
            'time' => $appointment->time ?? null,
            'grouping_capacity' => $appointment->grouping_capacity ?? null,
            'grouping_enabled' => $appointment->grouping_enabled ?? null,
            'create'=>$this->created_at,
            'location' =>json_decode($appointment["location"]),
            'description' => $this->description,
            'url' => $this->url,
            'personal_information'=>$this->personal_information,
            'additional_info'=>$this->additional_info,
            'phone'=>$this->phone,
            'email'=>$this->email,
            'end_date'=>$this->end_date,
            'other_info'=>$this->other_info,
            'dates'=>$this->datesall,
            'union' => $this->union,
            'contract' => $this->contract,
            'production' => $dataProduction,
            'cover'=> $coverUrl,
            'cover_thumbnail' => $coverThumb,
            'id_cover'=>$this->resources()->where('resource_type','=','App\Models\Auditions')
                    ->where('type','=','cover')
                    ->get()[0]['id'] ?? null,
            'status' => $this->status,
            'online'=>$this->online,
            'user_id' => $this->user_id,
            'director' => $this->user->load('details'),
            'agency'=>$dataUserDet->agency_name ?? null,
            'roles' => $this->roles,
            'media' => $this->resources()
                ->where('resource_type','=','App\Models\Auditions')
                ->where('type','!=','cover')
                ->get(),
            'apointment' => $appoinmentResponse,
            'contributors' => $this->contributors,
            'banned' => $this->banned,
            'admin_id' => $admin_id
        ];

        if($this->online == 1){
            if($appointment){
                $return['submissions'] = $submissionsCount;
            }
            $return['has_ended'] = ($this->end_date && (Carbon::now('UTC')->format('Y-m-d') > $this->end_date)) || $this->end_date == null ? true : false; 
        }
        return $return;
    }
}
