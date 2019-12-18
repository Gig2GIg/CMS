<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tests\Unit\SlotTest;

class Appointments extends Model
{
    protected $fillable =[
        'slots',
        'date',
        'time',
        'location',
        'type',
        'length',
        'start',
        'end',
        'round',
        'status',
        'auditions_id',
        'group_no',
        'is_group_open'
    ];

    public function slot(){
        return $this->hasMany(Slots::class,'appointment_id');
    }

    public function auditions(){
        return$this->belongsTo(Auditions::class);
    }
}
