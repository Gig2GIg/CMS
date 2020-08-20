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
        'lat',
        'lng',
        'type',
        'length',
        'start',
        'end',
        'round',
        'grouping_capacity',
        'grouping_enabled',
        'status',
        'auditions_id',
        'group_no',
        'is_group_open'
    ];

    public function slot(){
        return $this->hasMany(Slots::class,'appointment_id');
    }

    public function auditions(){
        return $this->belongsTo(Auditions::class);
    }

    public function userSlots(){
        return $this->belongsTo(UserSlots::class, 'id', 'appointment_id');
    }

    public function allUserSlots(){
        return $this->hasMany(UserSlots::class, 'appointment_id');
    }

    public function userAuditions(){
        return $this->hasMany(UserAuditions::class, 'appointment_id');
    }
}
