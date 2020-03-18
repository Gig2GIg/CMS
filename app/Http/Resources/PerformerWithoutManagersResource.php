<?php

namespace App\Http\Resources;

use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Appointments;
use App\Models\Feedbacks;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class PerformerWithoutManagersResource extends JsonResource
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
        
        $roles = explode(",", $this->rol_id);
        $rolanme = Roles::whereIn('id', $roles)->get()->pluck('name');

        $slot = $this->slot_id;
        if ($slot != null) {
            $repoSlot = new SlotsRepository(new Slots());
            $dataSlots = $repoSlot->find($slot);
            $dataHour = $dataSlots->time;
        }

        $userRepo = new UserRepository(new User());
        $userData = $userRepo->find($this->user_id);

        $return =  [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'auditions_id' => $dataRepo->auditions->id,
            'rol' => $this->rol_id,
            'rol_name' => $rolanme ?? null,
            'user_id' => $this->user_id,
            'name' => $userData->details->first_name . " " . $userData->details->last_name,
            'image' => $userData->image->url,
            'thumbnail' => $userData->image->thumbnail,
            'email' => $userData->email ? $userData->email : null,
            'birth' => $userData->details->birth ?? null,
            'title' => $dataRepo->auditions->title,
            'date' => $dataRepo->date,
            'hour' => $dataHour,
            'union' => $dataRepo->auditions->union,
            'contract' => $dataRepo->auditions->contract,
            'production' => $dataProduction,
            'media' => $media[0] ?? null,
            'media_thumbnail' => $url_thumb[0] ?? null,
            'round' => $dataRepo->round
        ];
        
        return $return;
    }
}
