<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuditions extends Model
{
    protected $fillable =[
        'user_id','appointment_id','type','rol_id','slot_id','group_no','rejected','has_manager'
    ];

    public function appointments(){
        return $this->belongsTo(Appointments::class, 'appointment_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function slot(){
        return $this->belongsTo(Slots::class, 'slot_id');
    }
}
