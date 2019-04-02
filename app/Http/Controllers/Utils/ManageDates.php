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
        $date_explode = explode(" ",$date);
        $expire_time = $date_explode[1]?? '01'.' '.$date_explode[2] ?? '01'.' '.$date_explode[3] ?? '1970';
        return  date('Y-m-d', strtotime($expire_time));

    }
}
