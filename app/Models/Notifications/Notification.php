<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'code',
        'type',
        'notificationable_type',
        'notificationable_id'
    ];
    
    public function notificationable(){
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


