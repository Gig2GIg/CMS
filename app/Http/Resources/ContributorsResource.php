<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Models\UserDetails;
use App\Models\User;
use App\Models\AuditionContributors;

class ContributorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $userDataRepo = new UserDetailsRepository(new UserDetails());
        $data = $userDataRepo->findbyparam('user_id',$this->user_id);

        return [
            'user_id' => $this->user_id,
            'audition_id' => $this->auditions_id,
            'title' => $this->auditions->title,
            'date' => $this->auditions->date,
            'time' => $this->auditions->time,
        ];
    }
}
