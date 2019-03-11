<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-10
 * Time: 18:10
 */

namespace  App\Http\Controllers\Utils;


use Illuminate\Support\Facades\Log;

class SendMail
{

    /**
     * SendMail constructor.
     */
    public function __construct()
    {
    }

    public function send($password,$user)
    {
        $email = new \SendGrid\Mail\Mail();

        $email->setFrom(env('SUPPORT_EMIAL'));
        $email->setSubject('Recover Password');
        $email->addTo($user['email']);
        $email->addContent("text/html","Your new password is: <strong>" .
            $password."</strong><br/>Please, change the password now.");

        $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
        try{
            $response = $sendgrid->send($email);
            return $response->statusCode() === 202 ? true : false;
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return false;
        }

    }
}
