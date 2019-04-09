<?php

namespace App\Models\Notifications;
use Illuminate\Database\Eloquent\Model;

class NotificationSettingUser extends Model
{
    protected $table = 'notification_setting_user';

    protected $fillable = [
        'status',
        'user_id',
        'notification_setting_id',
        'code'
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

