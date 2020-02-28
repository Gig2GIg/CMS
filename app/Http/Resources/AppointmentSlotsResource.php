<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentSlotsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected $type;

    public function __construct($resource, $type)
    {
        parent::__construct($resource);
        $this->type = $type;
    }

    public function toArray($request)
    {
        $slotsData = array();
        if ($this->type == 1) {
            $temp = $this->slot()
                ->where('is_walk', '=', '0')
                ->where('status', '=', '0')
                ->with('userSlots')
                ->get();
        } else {
            $temp = $this->slot()
                ->where('is_walk', '=', '1')
                ->where('status', '=', '0')
                ->with('userSlots')
                ->get();
        }

        foreach ($temp as $e) {
            $elem = $e->toArray();
            if(count($elem['user_slots']) != 0){
                if($elem['user_slots'][0]['status'] != 'reserved'){
                    array_push($slotsData, $elem);
                }
            }else{
                array_push($slotsData, $elem);
            }
        }

        return [
            'start' => $this->start,
            'end' => $this->end,
            'duration' => $this->length,
            'slots' => $slotsData
        ];
    }
}
