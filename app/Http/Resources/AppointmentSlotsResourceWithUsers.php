<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class AppointmentSlotsResourceWithUsers extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected $type;

    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $slotsData = array();
        
        $temp = $this->slot()
                ->with(['userSlot.user:id,email', 'userSlot.user.details', 'userSlot.user.image'])
                ->get();

        return [
            'start' => $this->start,
            'end' => $this->end,
            'duration' => $this->length,
            'slots' => $temp
        ];
    }
}
