<?php
namespace App\Http\Controllers\Utils;
use App\Http\Exceptions\NotificationException;

use App\Http\Controllers\Utils\LogManger;

class PushNotifications
{
    protected $log;

    public function __construct()
    {
        $this->log = new LogManger();   
    }

    public function send($message, $user)
    {
        try {
            fcm()
            ->to([$user->pushkey])
            ->notification([
                'message' => $message,
                'body'  => $message,
            ])
            ->send();

        } catch (NotificationException $exception) {
            $this->log->error($exception->getMessage());
        }
    }
}
