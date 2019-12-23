<?php

namespace App\Http\Resources;

use App\Http\Repositories\OnlineMediaAuditionsRepository;
use App\Models\OnlineMediaAudition;
use DemeterChain\A;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditionListByPerformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $url_media = $this->resources
            ->where('type', 'cover')
            ->where('resource_type', 'App\Models\Auditions')
            ->pluck('url');

        $appointmentIds = $this->appointment()->get()->pluck('id');
        $videoRepo = new OnlineMediaAuditionsRepository(new OnlineMediaAudition());
        $videoData = $videoRepo->findbyparam('performer_id', $request->id)
            ->where('type', 'video')
            ->whereIn('appointment_id', $appointmentIds)
            ->count();

        return [
            'id' => $this->id,
            "title" => $this->title,
            'create' => $this->created_at,
            "description" => $this->description,
            "url" => $this->url,
            'phone' => $this->phone,
            'email' => $this->email,
            "status" => $this->status,
            "cover" => $url_media[0] ?? null,
            "videos" => $videoData ?? null,
            // 'other_info' => $this->other_info,
            // 'additional_info' => $this->additional_info,
            // "union" => $this->union,
            // "contract" => $this->contract,
            // 'personal_information' => $this->personal_information,
            // "online" => $this->online,
            // "user_id" => $this->user_id,
        ];
    }
}
