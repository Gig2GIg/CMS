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
    
    public function notifications(){
        return $this->morphTo();
    }


    public function users()
    {
        return $this->hasManyThrough(
            Notification::class,
            NotificationUserSetting::class,
            'notification_id', 
            'id'
        );
    }
    
}


