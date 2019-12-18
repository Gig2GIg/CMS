<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserManager extends Model
{
    protected $fillable =[
        'name',
        'company',
        'email',
        'type',
        'notifications',
        'user_id',
    ];
}
