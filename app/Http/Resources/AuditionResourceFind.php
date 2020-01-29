<?php

/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-27
 * Time: 11:48
 */

namespace App\Http\Resources;

use App\Http\Repositories\AppointmentRepository;
use App\Models\Appointments;
use App\Models\Resources;
use App\Models\Roles;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditionResourceFind extends JsonResource
{
    public function toArray($request)
    {
        $roles = new Roles();
        $roles_data = $roles->where('auditions_id', $this->id)->get();
        $countRoles = $roles->where('auditions_id', $this->id)->count();

        $media = new Resources();
        $url_media = $media
            ->where('type', 'cover')
            ->where('resource_id', $this->id)
            ->where('resource_type', 'App\Models\Auditions')
            ->pluck('url');

        $appointmentRepo = new AppointmentRepository(new Appointments());
        $appointment = $appointmentRepo->findbyparam('auditions_id', $this->id)->first();

        // $this->roles->each(function ($item) {
        //     $item->image;
        // });

        // $roles->each(function ($item) {
        //     $item->image;
        // });

        $roles = $roles_data->each(function ($item) {
            $item->image;
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'create' => $this->created_at,
            "description" => $this->description,
            'url' => $this->url,
            'personal_information' => $this->personal_information,
            'additional_info' => $this->additional_info,
            'phone' => $this->phone,
            'email' => $this->email,
            'other_info' => $this->other_info,
            "union" => $this->union,
            "contract" => $this->contract,
            "production" => explode(',', $this->production),
            "status" => $this->status,
            "online" => $this->online,
            "user_id" => $this->user_id,
            "cover" => $url_media[0] ?? null,
            "number_roles" => $countRoles,
            "roles" => $roles,
            // "roles" => $this->roles,
            "location" => (isset($appointment->location) && $appointment->location != '') ? json_decode($appointment->location) : null,
        ];
    }
}
