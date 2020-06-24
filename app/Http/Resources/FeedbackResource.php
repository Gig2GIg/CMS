<?php

namespace App\Http\Resources;

use App\Http\Repositories\UserRepository;
use App\Models\User;
use App\Models\Appointments;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userRepo = new UserRepository(new User());
        $round = Appointments::select('round')->where('id',$this->appointment_id)->first();

        if($this->evaluator_id && $this->evaluator_id != null){
            $userData = $userRepo->find($this->evaluator_id);
            $name = $userData->details->first_name . " " . $userData->details->last_name;
        }else{
            $name = "";
        }

        return [
            'id'=>$this->id,
            'auditions_id'=>$this->auditions_id,
            'user_id' =>$this->user_id,
            'evaluator_id'=>$this->evaluator_id,
            'evaluator_name'=>$name,
            'evaluation'=>$this->evaluation,
            'rating'=>$this->rating,
            'callback'=>$this->callback,
            'work'=>$this->work,
            'favorite'=>$this->favorite,
            'comment'=>$this->comment,
            'round' => $round->round,
        ];
    }
}
