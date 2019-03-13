<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-06
 * Time: 13:59
 */

namespace App\Http\Controllers\Utils;


use Illuminate\Support\Facades\Log;

class LogManger
{
    public function error($message, $ref = ''){
        Log::error($message ." ". $ref);
    }

    public function info($message, $ref = ''){
        Log::info($message." ". $ref);
    }
}
