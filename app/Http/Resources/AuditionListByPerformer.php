<?php

namespace App\Http\Resources;

use App\Http\Repositories\AuditionVideosRepository;
use App\Http\Repositories\OnlineMediaAuditionsRepository;
use App\Models\AuditionVideos;
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
            ->where('resource_type', 'App\Models\Auditions');
        
        $media = $url_media->pluck('url');
        $url_thumb = $url_media->pluck('thumbnail');
        $cover_name = $url_media->pluck('name');

        $appointmentIds = $this->appointment()->get()->pluck('id');
        $videoRepo = new OnlineMediaAuditionsRepository(new OnlineMediaAudition());
        $onlineVideoCount = $videoRepo->findbyparam('performer_id', $request->id)
            ->where('type', 'video')
            ->whereIn('appointment_id', $appointmentIds)
            ->count();

        $offlineVideoRepo = new AuditionVideosRepository(new AuditionVideos());
        $offlineVideoCount = $offlineVideoRepo->findbyparam('user_id', $request->id)
            ->whereIn('appointment_id', $appointmentIds)
            ->count();

        $totalcount = $onlineVideoCount + $offlineVideoCount;
        return [
            'id' => $this->id,
            "title" => $this->title,
            'create' => $this->created_at,
            "description" => $this->description,
            "url" => $this->url,
            'phone' => $this->phone,
            'email' => $this->email,
            'end_date' => $this->end_date,
            "status" => $this->status,
            "cover" => $media[0] ?? null,
            "cover_name" => $cover_name[0] ?? null,
            "cover_thumbnail" =>  $url_thumb[0] ?? null,
            "videos" => $totalcount ?? null,
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
