<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Appointments;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AuditionCasterResponse extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $user = new User();
        $uData = $user->with(['details','image'])->where('id', $this->user_id)->first();
        $admin_data = NULL;
        $admin_id = NULL;

        if($uData){
            $admin_id = $uData->invited_by;
            if($admin_id)
                $admin_data = $user->with(['details','image'])->where('id', $uData->invited_by)->first();
        }

        $this->contributors->each(function ($item) use($user) {
            $userData = $user->with(['details','image'])->where('id', $item->user_id)->first();

            $userData->push($userData->details);
            $item['contributor_info'] = $userData;
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'create'=>$this->created_at,
            'user_id' => $this->user_id,
            'director' => $uData,
            'contributors' => $this->contributors,
            'admin_id' => $admin_id,
            'admin_data' => $admin_data,
        ];
    }
}
