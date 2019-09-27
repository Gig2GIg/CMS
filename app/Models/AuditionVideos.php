<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditionVideos extends Model
{
    protected $fillable = [
        'user_id',
        'appointment_id',
        'url',
        'contributors_id',
        'slot_id'
    ];
}
