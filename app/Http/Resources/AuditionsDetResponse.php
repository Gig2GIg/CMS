<?php

namespace App\Http\Resources;

use App\Http\Repositories\AppointmentRepository;
use App\Models\Appointments;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditionsDetResponse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $repoAppoinment = new AppointmentRepository(new Appointments());
        $appoinmentData = $repoAppoinment->find($this->appointment_id);
        return [
            'user_id' => $this->user_id,
            'auditions_id' => $appoinmentData->auditions->id,
            'appointment_id'=>$this->appointment_id,
            'title' => $appoinmentData->auditions->title,
            'date' => $appoinmentData->auditions->date,
            'time' => $appoinmentData->auditions->time,
            'slot_reserved'=>$this->slot_id,
            'create'=>$this->created_at,
        ];
    }
}
