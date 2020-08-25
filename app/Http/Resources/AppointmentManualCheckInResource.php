<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Appointments;

class AppointmentManualCheckInResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected $type;

    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $status = '';
        if($this['type'] == 1 && (count($this->user->userSlot) > 0 && $this->user->userSlot[0]->slot_id != NULL)){
            $status = 'Has Appointment';
        } elseif($this['type'] == 1 && (count($this->user->userSlot) > 0 && $this->user->userSlot[0]->slot_id == NULL)) {
            $status = 'Saved Audition';
        } else {
            $status = 'Requested Audition';
        }

        $audition = Appointments::find($this['appointment_id']);  

        return [
            'image' => $this->user->image->thumbnail ?? $this->user->image->url,
            'name' => $this->user->details->first_name . ' ' . $this->user->details->last_name,
            'status' => $status,
            'appointment_time' => $this->slot ? $this->slot->time : 'N/A',
            'is_checked_in' => $this->user->userSlot && count($this->user->userSlot) > 0 && $this->user->userSlot[0]->status == 'checked' ? true : false,
            'slot' => $this->slot ? $this->slot->id : NULL,
            'user' => $this->user->id,
            'auditions' => $audition->auditions_id,
            'rol' => $this['rol_id'],
            'appointment_id' => $this['appointment_id']
        ];
    }
}
