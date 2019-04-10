<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedbacks extends Model
{
    protected $fillable = [
        'auditions_id',
        'user_id',
        'evaluator_id',
        'evaluation',
        'callback',
        'work',
        'favorite'
    ];
}
