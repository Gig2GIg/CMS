<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Appointments;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserAuditions;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\PHP;

class AuditionAnalyticsResponse extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /*
        $this->contributors->each(function ($item) {
            $user = new UserRepository(new User());
            $userData = $user->find($item->user_id);
            $userData->push($userData->details);
            $item['contributor_info'] = $userData;
        });
        */

        /*
        $userDataRepo = new UserDetailsRepository(new UserDetails());
        $dataUserDet = $userDataRepo->findbyparam('user_id',$this->user_id);
        $this->roles->each(function($item){
            $item->image;
        });
        $dataProduction = explode(',',$this->production);
        $appointment = $this->appointment()->latest()->first();
        $slotsData = new SlotsRepository(new Slots());
        $slots = $slotsData->findbyparam('appointment_id',$appointment["id"])->get();
//        $location = isset($appointmentData->location) ? $appointment->location:'';
        $appoinmentResponse =  ['general' => $appointment, 'slots' => $slots];
        */
//        $appointments = $this->appointments->toArray();
        //Total Auditioners	Gender breakdown	Starred Performers



         return [
            [
                "1", "50", "35:15:5", "32"
            ],
            [
                "2", "50", "35:15:5", "32"
            ],
            [
                "3", "50", "35:15:5", "32"
            ]

        ];


        $appointments = $this->appointments;
        $csvArray = []; $i = 0;
        $appointments->each(function ($item)  use ($csvArray, $i) {

            $userAuditionRepo = new UserAuditionsRepository(new UserAuditions());
            $userAuditions = $userAuditionRepo->all()->where('appointment_id', $item->id);

            $csvArray[$i][] = $i+1;
            $csvArray[$i][] = count($userAuditions);
            $male = 0;
            $female = 0;
            $other = 0;
            $userAuditions->each(function ($uD) use ($male, $female, $other) {
                $userDetails = new UserDetailsRepository(new UserDetails());
                $dataUserDetails = $userDetails->findbyparam('user_id', $uD->user_id)->first();
                if($dataUserDetails->ge && $dataUserDetails->gender == "male") {
                    $male++;
                } else if($dataUserDetails && $dataUserDetails->gender == "female") {
                    $female++;
                } else {
                    $other++;
                }
            });
            $csvArray[$i][] = $male . ":" . $female . ":" . $other;
            $csvArray[$i][] = 10;


        });
        return $csvArray;
        /*
        return [
//            'id' => $this->id,
//            'appointments' => $appointments

            'appointment_id'=>$appointment["id"],
            'title' => $this->title,
            'date' => $appointment->date ?? null,
            'time' => $appointment->time ?? null,
            'create'=>$this->created_at,
            'location' =>json_decode($appointment["location"]),
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
            'cover'=>$this->resources()->where('resource_type','=','App\Models\Auditions')
                    ->where('type','=','cover')
                    ->get()[0]['url'] ?? null,
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
            'banned' => $this->banned

        ];
*/
    }
}
