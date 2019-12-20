<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstantFeedbackSettings extends Model
{
    protected $fillable = [
        'user_id',
        'comment'
    ];
}
