<?php

namespace App\Models\Notifications;
use Illuminate\Database\Eloquent\Model;

class NotificationSettingUser extends Model
{
    protected $fillable = [
        'status',
        'user_id',
        'notification_setting_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function notification_setting()
    {
        return $this->belongsTo(NotificationSetting::class);
    }
}

