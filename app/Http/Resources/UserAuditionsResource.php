<?php

namespace App\Http\Resources;

use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Models\Appointments;
use App\Models\Feedbacks;
use App\Models\Roles;
use App\Models\Slots;
use Illuminate\Http\Resources\Json\JsonResource;
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

        $round = Appointments::select('round','status')->where('id', $this->appointment_id)->first();
        $dataHour = null;
        $dataProduction = explode(",", $dataRepo->auditions->production);
        $url_media = $dataRepo->auditions->resources
            ->where('type', 'cover')
            ->where('resource_type', 'App\Models\Auditions')
            ->pluck('url');
        $rolanme = Roles::where('id', '=', $this->rol_id)->get()->pluck('name');
        $feedback_comment = Feedbacks::select('comment')->where('appointment_id', $this->appointment_id)->first();
        // print_r($feedback_comment);
        // die;
        // ===========================
        // $feedback_favorite = Feedbacks::select('favorite')->where('appointment_id', $this->appointment_id)->first();
        // if ($feedback_favorite == null) {
        //     $favorite = new stdClass();
        //     $favorite->favorite = 0;
        // } else {
        //     $favorite = $feedback_favorite;
        // }
        // ===========================
        $slot = $this->slot_id;
        if ($slot != null) {
            $repoSlot = new SlotsRepository(new Slots());
            $dataSlots = $repoSlot->find($slot);
            $dataHour = $dataSlots->time;
        }
        $return =  [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'appointment_id' => $this->appointment_id,
            'auditions_id' => $dataRepo->auditions->id,
            'online' => $dataRepo->auditions->online,
            'rol' => $this->rol_id,
            'rol_name' => $rolanme[0] ?? null,
            'id_user' => $dataRepo->auditions->user_id,
            'title' => $dataRepo->auditions->title,
            'date' => $dataRepo->date,
            'hour' => $dataHour,
            'union' => $dataRepo->auditions->union,
            'contract' => $dataRepo->auditions->contract,
            'production' => $dataProduction,
            'media' => $url_media[0] ?? null,
            'number_roles' => count($dataRepo->auditions->roles),
            'round' => $round->round,
            // ===========================
            'comment' => $feedback_comment['comment'],
            'status' => $dataRepo->status,
            // 'favorite' => $favorite->favorite,
            'assign_no' => $this->assign_no ?? NULL,
            // ===========================
        ];
        return $return;
    }
}
