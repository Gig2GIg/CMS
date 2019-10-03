<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $fillable =[
        'title',
        'appointment_id',
        'user_id',
        'setUser_id'
    ];
}
