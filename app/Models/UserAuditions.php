<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuditions extends Model
{
    protected $fillable =[
        'user_id','appointment_id','type','rol_id','slot_id','group_no','rejected'
    ];

    public function appointments(){
        return $this->belongsTo(Appointments::class);
    }


}
