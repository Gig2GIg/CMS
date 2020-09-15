<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\CasterTeam;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitedUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $user = User::findOrFail($this->member_id);

        return [
            'id' => $user->id,
            'details' => $user->details,
            'email' => $user->email,
            'image' => $user->image,
            'is_active' => $user->is_active,
            'is_premium' => $user->is_premium,
            'is_profile_completed' => $user->is_profile_completed,
            'is_invited' => CasterTeam::where('admin_id', $user->id)->count() == 0 ? true : false,
        ];
    }
}
