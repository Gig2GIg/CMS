<?php

namespace App\Http\Resources;

use App\Http\Repositories\UserRepository;
use App\Models\User;
use App\Models\Appointments;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AuditionLogResource extends JsonResource
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

        $userData = $userRepo->find($this->edited_by);
        if($userData->details){
            $name = $userData->details->first_name . " " . $userData->details->last_name;
            $email = $userData->email;
        }else{
            $name = $this->edited_by;
            $email = 'N/A';
        }
        
        return [
            'key'=>$this->key,
            'old_value' =>$this->old_value,
            'new_value'=>$this->new_value,
            'editor_email'=>$email,
            'edited_by'=>$name,
            'created_at'=>Carbon::parse($this->created_at)->format('Y-m-d H:i:s')
        ];
    }
}
