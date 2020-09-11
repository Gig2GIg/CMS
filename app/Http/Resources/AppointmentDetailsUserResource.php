<?php

namespace App\Http\Resources;

use App\Http\Repositories\AuditionVideosRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\AuditionVideos;
use App\Models\Feedbacks;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentDetailsUserResource extends JsonResource
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
        if($this->roles_id !== null) {
            $rol = new RolesRepository(new Roles());
            $rolData = $rol->find($this->roles_id);
        }
        $videosRepo = new AuditionVideosRepository(new AuditionVideos());
        $videoData = $videosRepo->findbyparam('user_id',$this->user_id)->first();
        $feedbackRepo = new FeedbackRepository(new Feedbacks());
        $feedbackData = $feedbackRepo->findbyparams(['user_id'=>$this->user_id, 'appointment_id'=>$this->appointment_id])->latest()->first();


        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'email' => $userData->email,
            'rol'=>$this->roles_id,
            'rol_name' => $rolData->name ?? null,
            'video'=> $videoData->url ?? null,
            'video_thumbnail' => $videoData->thumbnail ?? null,
            'image' => $userData->image->url,
            'thumbnail' => $userData->image->thumbnail,
            'name' => sprintf('%s %s', $userData->details->first_name, $userData->details->last_name),
            'user_city'=> sprintf('%s, %s', $userData->details->city, $userData->details->state),
            'details' => $userData->details,
            'favorite'=>$this->favorite,
            'slot_id'=>$this->slots_id,
            'feedback'=>$feedbackData,
        ];
    }
}
