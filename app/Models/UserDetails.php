<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $fillable =[
      'last_name','first_name','type', 'address','city','birth','location','state','user_id','profesion','stage_name'
    ];
}
