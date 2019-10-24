<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineMediaAudition extends Model
{
    protected $fillable =[
        'appointment_id',
        'performer_id',
        'url',
        'thumbnail',
        'type',
        'name'
    ];
}
