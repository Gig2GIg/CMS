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

    protected $log;

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
    public function sendPushNotification($audition= null, $type , $user = null, $title = null)
    {
        SendNotifications::send(
            $audition,
            $type,
            $user,
            $title
        );
    }

}
