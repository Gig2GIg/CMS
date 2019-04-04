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
    public function __construct($resource,$type)
    {
        parent::__construct($resource);
        $this->type=$type;
    }

    public function toArray($request)
    {
        if($this->type==1){
            $slotsData = $this->slot()->where('is_walk','=','0')->where('status','=','0')->get();
        }else{
            $slotsData = $this->slot()->where('is_walk','=','1')->where('status','=','0')->get();
        }
        return [

            'start' => $this->start,
            'end' => $this->end,
            'duration'=>$this->length,
            'slots'=>$slotsData
        ];
    }
}
