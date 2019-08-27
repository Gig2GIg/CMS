<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalCast extends Model
{
    //
    protected $fillable =[
        'audition_id',
        'performer_id',
        'rol_id'
    ];
}
