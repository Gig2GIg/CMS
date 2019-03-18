<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slots extends Model
{
    protected $fillable =[
        'time',
        'status',
        'id_appointment'
    ];

    public function appointment(){
        return $this->belongsTo(Appointments::class);
    }
}
