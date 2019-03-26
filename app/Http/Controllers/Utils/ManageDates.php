<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-26
 * Time: 15:41
 */

namespace App\Http\Controllers\Utils;


class ManageDates
{
    public function transformDate($date)
    {
        $expire_time = substr($date, 0, strpos($date, '('));
        return  date('Y-m-d', strtotime($expire_time));

    }
}
