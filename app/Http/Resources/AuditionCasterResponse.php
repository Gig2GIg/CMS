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
use Illuminate\Support\Collection;

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
        $collection = new Collection();

        $uData = $user->with(['details','image'])->where('id', $this->user_id)->first();

        $collection->push(collect($uData));

        if($uData && $uData->invited_by){
            $admin_data = $user->with(['details','image'])->where('id', $uData->invited_by)->first();
            $collection->push(collect($admin_data));
        }

        $this->contributors->each(function ($item) use($user, $collection) {
            $userData = $user->with(['details','image'])->where('id', $item->user_id)->first();
            $collection->push(collect($userData));
        });

        return $collection;
    }
}
