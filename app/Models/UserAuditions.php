<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuditions extends Model
{
    protected $fillable =[
        'user_id','appointment_id','type','rol_id','slot_id'
    ];

    public function auditions(){
        return $this->belongsTo(Auditions::class);
    }

}
