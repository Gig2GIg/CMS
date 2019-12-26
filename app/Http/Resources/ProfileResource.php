<?php

namespace App\Http\Resources;

use App\Models\AuditionVideos;
use App\Models\UserAuditions;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {


        $assign_number = UserAuditions::where('appointment_id', $request->appointment_id)
            ->select('assign_no')
            ->where('user_id', $this->id)
            ->first();

        // $assign_no = $this->details;

        // userAuditions

        // check if user has uploaded video before or not
        $videoRepo = new AuditionVideos();
        $videoData = $videoRepo->where('user_id', $this->id)
            ->where('appointment_id', $request->appointment_id)->get();
        // ->groupBy('user_id')
        // ->pluck('user_id');

        if ($videoData->count() == 0) {
            $has_uploaded = 0;
        } else {

            $has_uploaded = 1;
        }


        return [
            'app' => $request->appointment_id,
            'assign_number' => $assign_number->assign_no ?? null,
            'has_uploaded' => $has_uploaded,
            // 'assign_no' => $assign_no->assign_no ?? null,
            'id' => $this->id,
            'details' => $this->details,
            'education' => $this->educations,
            'credits' => $this->credits,
            'aparence' => $this->aparence,
        ];
    }
}
