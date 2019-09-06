<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-10
 * Time: 18:10
 */

namespace App\Http\Controllers\Utils;


use App\Http\Controllers\NotificationManagementController;
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


    public function sendCode($user, $data)
    {
        try {
            $push = new NotificationManagementController();
            $email = new Mail();

            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Add them to your Talent Database');
            $email->addTo($user->email);
            $content = sprintf('<strong>%s</strong> has shared <strong>%s</strong> with you! Add them to your Talent Database with code: <strong>%s</strong>. ',
                $data['sender'],
                $data['performer'],
                $data['code']);

            $email->addContent("text/html", $content);
            $push->sendPushNotification(null,'cms_to_user',$user,$content);
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

    
    public function sendPerformance($user, $data)
    {
        try {
            $push = new NotificationManagementController();
            $email = new Mail();
            
            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Your appointment time to audition'.  $data['audition_title'] . 'is update');
            $email->addTo($user->email);
            $content = sprintf('<strong>%s</strong> Hello <strong>%s</strong> Your appointment time to audition'.  $data['audition_title'] . 'is update <strong>%s</strong>'. 'to'. $data['slot_time']);

            $this->log->info('<strong>%s</strong> Hello <strong>%s</strong> Your appointment time to audition'.  $data['audition_title'] . 'is update <strong>%s</strong>'. 'to'. $data['slot_time']);
            $email->addContent("text/html", $content);
            $push->sendPushNotification(null,'cms_to_user',$user,$content);
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
