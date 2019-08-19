<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-10
 * Time: 18:10
 */

namespace App\Http\Controllers\Utils;


use App\Http\Exceptions\SendEmailException;

use SendGrid\Mail\Mail;

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

    public function send($password, $emailTo)
    {
        try {
            $email = new Mail();

            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Recover Password');
            $email->addTo($emailTo);
            $email->addContent("text/html", "Your new password is: <strong>" .
                $password . "</strong><br/>Please, change the password now.");

            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function sendManager($emailTo, $data)
    {
        try {
            $email = new Mail();
            $content = sprintf("Your client: <strong> %s</strong> wants to attend the audition:<a href='%s'> <strong>%s</strong></a>, check his agenda to set the time of the appointment",
                $data['name'],
                $data['url'],
                $data['audition']);
            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Check Auditions');
            $email->addTo($emailTo);
            $email->addContent("text/html", $content);

            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function sendContributor($emailTo, $name)
    {
        try {
            $email = new Mail();

            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('You have invited to audition');
            $email->addTo($emailTo);
            $email->addContent("text/html", "You have been invited to participate as a contributor in the audition: <strong> " . $name . "</strong> ");

            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return false;
        }
    }
}
