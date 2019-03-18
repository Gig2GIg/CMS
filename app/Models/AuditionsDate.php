<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditionsDate extends Model
{
    protected $fillable =[
        'type',
        'to',
        'from',
        'audition_id'
    ];

    public function auditions(){
        $this->belongsTo(Auditions::class);
    }
}
