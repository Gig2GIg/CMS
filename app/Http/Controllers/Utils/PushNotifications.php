<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotificationException;
use Illuminate\Support\Collection;

class PushNotifications
{
    protected $log;

    public function __construct()
    {
        $this->log = new LogManger();
    }

    public static function send($message, $user)
    {
        try {
            $tokenArray = new Collection();
            $user->pushkey->each(function ($user_token_detail) use ($tokenArray) {
                if($user_token_detail->device_token){
                    $tokenArray->push($user_token_detail->device_token);
                }
            });
            $tokens = $tokenArray->toArray();

            fcm()
                ->to($tokens)
                ->notification([
                    'title' => $message,
                    'body' => $message,
                ])
                ->send();
        } catch (NotificationException $exception) {
            $log->error($message);
            $log->error($exception->getMessage());
        }

    }
}
