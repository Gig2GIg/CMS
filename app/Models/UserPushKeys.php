<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPushKeys extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'device_token',
    ];

    public function users()
    {
        $this->belongsTo(User::class);
    }

}
