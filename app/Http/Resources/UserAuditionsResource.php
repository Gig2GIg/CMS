<?php

namespace App\Http\Resources;

use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Models\Appointments;
use App\Models\Feedbacks;
use App\Models\Roles;
use App\Models\Slots;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use stdClass;

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

        $repoAppointment = new AppointmentRepository(new Appointments());
        $dataRepo = $repoAppointment->find($this->appointment_id);
        $dataHour = null;
        $dataProduction = explode(",", $dataRepo->auditions->production);
        $url_media = $dataRepo->auditions->resources
            ->where('type', 'cover')
            ->where('resource_type', 'App\Models\Auditions');

        $media = $url_media->pluck('url');
        $url_thumb = $url_media->pluck('thumbnail');
        $cover_name = $url_media->pluck('name');

        $roles = explode(",", $this->rol_id);
        $rolanme = Roles::whereIn('id', $roles)->get()->pluck('name');
        
        // $feedback_comment = Feedbacks::select('comment')->where('appointment_id', $this->appointment_id)->first();

        $slot = $this->slot_id;
        if ($slot != null) {
            $repoSlot = new SlotsRepository(new Slots());
            $dataSlots = $repoSlot->find($slot);
            $dataHour = $dataSlots->time;
        }
        $return =  [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'auditions_id' => $dataRepo->auditions->id,
            'online' => $dataRepo->auditions->online,
            'rol' => $this->rol_id,
            'rol_name' => $rolanme ?? null,
            'id_user' => $dataRepo->auditions->user_id,
            'end_date'=> $dataRepo->auditions->end_date,
            'title' => $dataRepo->auditions->title,
            'date' => $dataRepo->date,
            'hour' => $dataHour,
            'union' => $dataRepo->auditions->union,
            'contract' => $dataRepo->auditions->contract,
            'production' => $dataProduction,
            'media' => $media[0] ?? null,
            'media_thumbnail' => $url_thumb[0] ?? null,
            'media_name' => $cover_name[0] ?? null,
            'number_roles' => count($dataRepo->auditions->roles),
            'round' => $dataRepo->round,
            'grouping_capacity' => $dataRepo->grouping_capacity ?? null,
            'grouping_enabled' => $dataRepo->grouping_enabled ?? null,
            // ===========================
            'comment' => isset($this->comment) && $this->comment ? $this->comment : "",
            'status' => $dataRepo->status,
            'assign_no' => $this->assign_no ?? NULL,
            // ===========================
        ];

        if($dataRepo->auditions->online == 1){
            $return['has_ended'] = ($dataRepo->auditions->end_date && (Carbon::now('UTC')->format('Y-m-d') > $dataRepo->auditions->end_date)) || $dataRepo->auditions->end_date == null ? true : false; 
        }
        return $return;
    }
}
