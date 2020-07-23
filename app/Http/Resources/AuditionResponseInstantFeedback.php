<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use DemeterChain\A;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuditionResponseInstantFeedback extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $count = count($this->roles);
        $dataProduction = explode(',', $this->production);
        $url_media = $this->resources
            ->where('type', 'cover')
            ->where('resource_type', 'App\Models\Auditions');
        
        $media = $url_media->pluck('url');
        $url_thumb = $url_media->pluck('thumbnail');
        $cover_name = $url_media->pluck('name');

        $userDataRepo = new UserDetailsRepository(new UserDetails());
        $data = $userDataRepo->findbyparam('user_id', $this->user_id);
        $appointment = $this->appointment()->latest()->first();


        $start_date =  $this->created_at;
        $end_date =  Carbon::now();
        $interval = $end_date->diff($start_date);
        $day = $interval->format('%dd');
        $hour = $interval->format('%hh');
        $min = $interval->format('%im');
        $sec = $interval->format('%ss');

        if ($day != 0) {
            $posted_before = $day;
        } else if ($hour != 0) {
            $posted_before = $hour;
        } else if ($min != 0) {
            $posted_before = $min;
        } else {
            $posted_before = $sec;
        }

        return [
            'id' => $this->id,
            'appointment_id' => $appointment->id,
            'round' => $appointment->round,
            'id_user' => $this->user_id,
            'agency' => $data->agency_name ?? null,
            "title" => $this->title,
            "date" => $appointment->date ?? null,
            'create' => $this->created_at,
            "time" => $appointment->time ?? null,
            'grouping_capacity' => $appointment->grouping_capacity ?? null,
            'grouping_enabled' => $appointment->grouping_enabled ?? null,
            "location" => json_decode($appointment->location),
            "description" => $this->description,
            "url" => $this->url,
            'personal_information' => $this->personal_information,
            'phone' => $this->phone,
            'email' => $this->email,
            'end_date' => $this->end_date,
            'other_info' => $this->other_info,
            'additional_info' => $this->additional_info,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => $dataProduction,
            "status" => $this->status,
            "online" => $this->online,
            "user_id" => $this->user_id,
            "cover" => $media[0] ?? null,
            "cover_name" => $cover_name[0] ?? null,
            "cover_thumbnail" =>  $url_thumb[0] ?? null,
            "number_roles" => $count,
            "posted_before" => $posted_before,

        ];
    }
}
