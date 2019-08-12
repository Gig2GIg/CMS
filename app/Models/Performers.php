<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Performers extends Model
{
    //
    protected $fillable =[
        'performer_id',
        'director_id',
        'uuid'
    ];
}
