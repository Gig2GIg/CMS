<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'code',
        'description',
        'type'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->using(UserNotificationSetting::class);
    }
    
}
