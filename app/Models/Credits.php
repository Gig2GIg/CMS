<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credits extends Model
{
    protected $fillable =['name','date','year','type','rol','production','month','user_id'];
}
