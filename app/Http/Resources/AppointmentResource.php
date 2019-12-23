<?php

namespace App\Http\Resources;

use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Repositories\InstantFeedbackRepository;
use App\Models\InstantFeedback;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $user = new UserRepository(new User());
        $userData = $user->find($this->user_id);
        $slot = new SlotsRepository(new Slots());
        $slotData = $slot->find($this->slots_id);

        $repo = new InstantFeedbackRepository(new InstantFeedback());
        $data = $repo->findbyparams([
            'appointment_id' => $slotData->appointment_id,
            'user_id' => $this->user_id
        ])->get();
        
        $is_feedback_sent = $data->count() == 0 ? 0 : 1;

        return [
            'user_id' => $this->user_id,
            'rol' => $this->roles_id,
            'image' => $userData->image->url,
            'name' => $userData->details->first_name . " " . $userData->details->last_name,
            'time' => $slotData->time,
            'favorite' => $this->favorite,
            'slot_id' => $this->slots_id,
            'is_feedback_sent' => $is_feedback_sent ?? null
        ];
    }
}
