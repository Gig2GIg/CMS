<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slots extends Model
{
    protected $fillable =[
        'time',
        'status',
        'is_walk',
        'number',
        'appointment_id'
    ];

    public function appointment(){
        return $this->belongsTo(Appointments::class);
    }

    public function userSlots(){
        return $this->hasMany(UserSlots::class);
    }
}
