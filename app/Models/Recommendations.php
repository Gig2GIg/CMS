<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendations extends Model
{
    protected $fillable = [
        'marketplace_id',
        'user_id',
        'audition_id',
    ];

}
