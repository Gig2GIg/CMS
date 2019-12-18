<?php
namespace App\Http\Exceptions;

use App\Http\Controllers\Utils\LogManger;

class NotificationException 
{
    protected $log;

    public function __construct()
    {
        $this->log = new LogManger();
    
    }

}
