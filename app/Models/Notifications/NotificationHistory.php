<?php
namespace App\Models\Notifications;
use Illuminate\Database\Eloquent\Model;

class NotificationHistory extends Model
{
    protected $table = 'notification_history';

    protected $fillable = [
        'title',
        'code',
        'status',
        'user_id',
        'message',
        'custom_data'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

