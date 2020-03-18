<?php

namespace App\Http\Resources;

use App\Http\Repositories\InstantFeedbackRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Models\InstantFeedback;
use App\Models\UserSlots;
use App\Models\User;
use App\Models\Slots;
use App\Models\Feedbacks;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckGroupStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        // user_details
        $userRepo = new UserRepository(new User());
        $userData = $userRepo->find($this->user_id);

        //Slot details and time
        if($this->slot_id && $this->slot_id != null){
            $slot = new SlotsRepository(new Slots());
            $slotData = $slot->find($this->slot_id);
            $time = $slotData->time;
        }else{
            $time = null;
        }

        // instant_feedback
        $instantFeedbackRepo = new InstantFeedbackRepository(new InstantFeedback());

        $instantFeedbackData = $instantFeedbackRepo->findbyparams(
            [
                'appointment_id' => $this->appointment_id,
                'user_id' => $this->user_id
            ]
        )->get();

        $is_feedback_sent = $instantFeedbackData->count() == 0 ? 0 : 1;


        // favorite FROM feedback table
        $feedbackRepo = new FeedbackRepository(new Feedbacks());
        $isFeefbackFavorite = $feedbackRepo->findbyparams([
            'appointment_id' => $this->appointment_id,
            'user_id' => $this->user_id
        ])->value('favorite');

        $return =  [
            'id' => $userData->details->id,
            'first_name' => $userData->details->first_name,
            'last_name' => $userData->details->last_name,
            'url' => $userData->details->url,
            'address' => $userData->details->address,
            'city' => $userData->details->city,
            'state' => $userData->details->state,
            'birth' => $userData->details->birth,
            'user_id' => $userData->details->user_id,
            'group_no' => $this->group_no,
            'assign_no' => $this->assign_no,
            'assign_no_by' => $this->assign_no_by,
            'slot_id' => $this->slot_id,
            'time' => $time,
            'rol_id' => $this->rol_id,
            'appointment_id' => $this->appointment_id,
            'image' => $userData->image->url,
            'thumbnail' => $userData->image->thumbnail,
            'is_feedback_sent' => $is_feedback_sent,
            'favorite' => $isFeefbackFavorite ?? NULL
        ];
        return $return;
    }
}
