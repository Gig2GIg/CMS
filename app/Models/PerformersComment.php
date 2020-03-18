<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformersComment extends Model
{
    protected $fillable = [
        'appointment_id',
        'user_id',
        'evaluator_id',
        'slot_id',
        'comment'
    ];
}
