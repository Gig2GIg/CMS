<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class NotificationUserSetting extends Pivot
{
    protected $table = 'notification_user';

    protected $fillable = [
        'status',
        'user_id',
        'notification_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
