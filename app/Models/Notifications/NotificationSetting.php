<?php
namespace App\Models\Notifications;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'status',
        'code',
    ];
}

