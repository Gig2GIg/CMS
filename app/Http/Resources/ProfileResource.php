<?php

namespace App\Http\Resources;

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
        // dd($request);

        $assign_number = UserAuditions::where('appointment_id', $request->appointment_id)
        ->select('assign_no')
            ->where('user_id', $this->id)
            ->first();
            // ->get('assign_no');
        return [
            'app' => $request->appointment_id,
            'assign_number' => $assign_number->assign_no ?? null,
            'id' => $this->id,
            'details' => $this->details,
            'education' => $this->educations,
            'credits' => $this->credits,
            'aparence' => $this->aparence,
        ];
    }
}
