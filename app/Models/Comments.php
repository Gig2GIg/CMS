<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $fillable =[
        'body',
        'created_at',
        'post_id',
        'user_id'
    ];
   
}
