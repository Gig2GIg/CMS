<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedbacks extends Model
{
    protected $fillable = [
        'appointment_id',
        'user_id',
        'evaluator_id',
        'evaluation',
        'rating',
        'callback',
        'simple_feedback',
        'work',
        'favorite',
        'slot_id',
        'comment',
        'recommendation'
    ];

    public function tags(){
        return $this->hasMany(Tags::class, 'feedback_id');
    }
}
