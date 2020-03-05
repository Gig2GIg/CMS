<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPushKeys extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'device_token',
        'device_type'
    ];

    public function users()
    {
        $this->belongsTo(User::class);
    }

}
