<?php

namespace App\Http\Resources;

use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\ResourcesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Models\Appointments;
use App\Models\Resources;
use App\Models\Roles;
use App\Models\Slots;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class UserAuditionsResource extends JsonResource
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
        $appoinmentData = $repoAppoinment->find($this->appointment_id)->where('status',true)->first();
       $dataHour = null;
        $dataProduction = explode(",", $appoinmentData->auditions->production);
        $url_media = $appoinmentData->auditions->resources
            ->where('type', 'cover')
            ->where('resource_type', 'App\Models\Auditions')
            ->pluck('url');
        $rolanme = Roles::where('id', '=', $this->rol_id)->get()->pluck('name');
        $slot = $this->slot_id;
        if ($slot != null) {
            $repoSlot = new SlotsRepository(new Slots());
            $dataSlots = $repoSlot->find($slot);
            $dataHour = $dataSlots->time;
        }
        return [
            'id' => $this->id,
            'appointment' => $appoinmentData->id,
            'auditions_id' => $appoinmentData->auditions->id,
            'rol' => $this->rol_id,
            'rol_name' => $rolanme[0] ?? null,
            'id_user' => $appoinmentData->auditions->user_id,
            'title' => $appoinmentData->auditions->title,
            'date' => $appoinmentData->date,
            'hour' => $dataHour,
            'union' => $appoinmentData->auditions->union,
            'contract' => $appoinmentData->auditions->contract,
            'production' => $dataProduction,
            'media' => $url_media[0] ?? null,
            'number_roles' => count($appoinmentData->auditions->roles),
        ];
    }
}
