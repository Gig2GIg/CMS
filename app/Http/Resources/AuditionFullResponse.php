<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Slots;
use App\Models\User;
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
        Log::info( $this->contributors);
        $this->roles->each(function($item){
            $item->image;
        });
        $dataProduction = explode(',',$this->production);

        $appoinment = $this->appointment;
        $slotsData = new SlotsRepository(new Slots());
        $slots = $slotsData->findbyparam('appointment_id',$appoinment->id)->get();

        $appoinmentResponse =  ['general' => $this->appointment, 'slots' => $slots];
        return [
            'id' => $this->id,
            'title' => $this->title,
            'date' => $this->date,
            'time' => $this->time,
            'location' => explode(',',$this->location),
            'description' => $this->description,
            'url' => $this->url,
            'dates'=>$this->datesall,
            'union' => $this->union,
            'contract' => $this->contract,
            'production' => $dataProduction,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'roles' => $this->roles,
            'media' => $this->resources()->where('resource_type','=','App\Models\Auditions')->get(),
            'apointment' => $appoinmentResponse,
            'contributors' => $this->contributors
        ];
    }
}
