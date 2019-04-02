<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NotificationHistory extends Model
{
    protected $table = 'notification_history';

    protected $fillable = [
        'title',
        'code',
        'status',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

