<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuditionMedia extends Model
{
    protected $fillable = [
      'user_id',
      'auditions_id',
    ];
}
