<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\Notifications as SendNotifications;

use App\Http\Controllers\Utils\LogManger;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function getDataToken()
    {
        $log = new LogManger();
        $payload = auth()->payload();

// then you can access the claims directly e.g.

        $log->info($payload);
    }

    public function getUserLogging()
    {
        return Auth::user()->getAuthIdentifier();
    }

//use to send notifications
    public function sendPushNotification($object, $type)
    {
        $this->log->info("Send Notificatio by" . $object->title);
        SendNotifications::send(
            $object,
            $type
        );
    }

}
