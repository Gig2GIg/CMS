<?php

namespace App\Http\Resources;

//use App\Http\Repositories\AppointmentRepository;
//use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\InstantFeedbackRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserManagerRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserUnionMemberRepository;
use App\Models\Appointments;
//use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\InstantFeedback;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserManager;
use App\Models\UserUnionMembers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

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

        $appointment = Appointments::find($this->appointment_id);

        $repo = new InstantFeedbackRepository(new InstantFeedback());
        $instant_feedback = $repo->findbyparams([
            'appointment_id' => $slotData->appointment_id,
            'user_id' => $this->user_id,
        ])->get();

        $feedbackRepo = new FeedbackRepository(new Feedbacks());
        $feedback = Feedbacks::where([
            'appointment_id' => $this->appointment_id,
            'user_id' => $this->user_id,
            'evaluator_id' => Auth::id(),
        ])->first();

        $userManagerRepo = new UserManagerRepository(new UserManager());
        $userManager = $userManagerRepo->findbyparam('user_id', $this->user_id);

        $userRepo = new UserRepository(new User());
        $userData = $userRepo->find($this->user_id);
        $user = new UserResource($userData);


        $userUnionMemberRepo = new UserUnionMemberRepository(new UserUnionMembers());
        $userUnionMember = $userUnionMemberRepo->findbyparam('user_id', $this->user_id)->pluck('name')->toArray();
        $userUnionMember = array_unique($userUnionMember);
        $userUnionMemberString = count($userUnionMember) > 0 ? implode(',', $userUnionMember) :"";

        $is_feedback_sent = $instant_feedback->count() == 0 ? 0 : 1;

        return [
            'user_id' => $this->user_id,
            'rol' => $this->roles_id,
            'image' => $userData->image->url,
            'name' => $userData->details->first_name . " " . $userData->details->last_name,
            'time' => $slotData->time,
            'group_number' => $slotData->group_number ?? null,
            'favorite' => $feedback ? $feedback->favorite : null,
            // 'favorite' => $this->favorite,
            'slot_id' => $this->slots_id,
            'is_feedback_sent' => $is_feedback_sent ?? null,
            'email' => $userData->email ? $userData->email : null,
            'birth' => $userData->details->birth ?? null,
            'representation_name' => isset($userManager->name) ? $userManager->name : "",
            'representation_email' => isset($userManager->email) ? $userManager->email : "",
            'union_string' => $userUnionMemberString,
            'union_array' => $userUnionMember,
            'website' => isset($user->details->url) ? $user->details->url : "",
            'grouping_capacity' => $appointment ? $appointment->grouping_capacity : null,
            'grouping_enabled' => $appointment ? $appointment->grouping_enabled : null,
        ];
    }
}
