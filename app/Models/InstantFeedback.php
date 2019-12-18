<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstantFeedback extends Model
{
    protected $fillable = [
        'appointment_id',
        'user_id',
        'evaluator_id',
        'comment'
    ];
}
