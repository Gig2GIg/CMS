<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-10
 * Time: 18:10
 */

namespace  App\Http\Controllers\Utils;


use App\Http\Exceptions\SendEmailException;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class SendMail
{
    protected $log;
    /**
     * SendMail constructor.
     */
    public function __construct()
    {
        $this->log = new LogManger();
    }

    public function send($password,$emailTo)
    {
        $email = new \SendGrid\Mail\Mail();

        $email->setFrom(env('SUPPORT_EMIAL'));
        $email->setSubject('Recover Password');
        $email->addTo($emailTo);
        $email->addContent("text/html","Your new password is: <strong>" .
            $password."</strong><br/>Please, change the password now.");

        $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if($response->statusCode() === 202 ){
                return true;
            }else {
                $this->log->error($response->body()." ". $response->statusCode());
                return false;
            }
    }
}
