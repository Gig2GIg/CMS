<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    protected $fillable =[
      'appointment_id','title','time'
    ];
}
