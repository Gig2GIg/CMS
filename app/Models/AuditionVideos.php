<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditionVideos extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'appointment_id',
        'url',
        'contributors_id',
        'slot_id'
    ];
}
