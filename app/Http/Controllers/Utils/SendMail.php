<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-10
 * Time: 18:10
 */

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\NotificationManagementController;
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
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function sendManager($emailTo, $data)
    {
        try {
            $email = new Mail();
            // $content = sprintf("Your client: <strong> %s</strong> wants to attend the audition:<a href='%s'> <strong>%s</strong></a>, check his agenda to set the time of the appointment",
            // $data['name'],
            // $data['url'],
            // $data['audition']);
            $content = sprintf("%s has requested an appointment for %s",
                $data['name'],
                $data['audition'],
                $data['url']);
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
        } catch (\Exception $exception) {
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
        } catch (\Exception $exception) {
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

            $content = sprintf('<strong>%s</strong> has shared <strong>%s</strong> with you! Add them to your Talent Database with code: <strong>%s</strong>. <br /> <p>Follow this <a href="%s" target="_blank">Link</a> to visit the Talent Database page.<p>',
                $data['sender'],
                $data['performer'],
                $data['code'],
                $data['link']);

            $contentpush = sprintf('%s has shared %s with you! Add them to your Talent Database with code: %s. ',
            $data['sender'],
            $data['performer'],
            $data['code']);
                
            $email->addContent("text/html", $content);
            $push->sendPushNotification(null, 'cms_to_user', $user, $contentpush);
            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function sendTalentDatabaseMail($mail, $data)
    {
        try {
            $email = new Mail();
            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Performer Talent Database Details');
            $email->addTo($mail);

            $content = sprintf('<strong>%s</strong> has shared <strong>%s</strong> with you! <br /> <p>Follow this <a href="%s" target="_blank">Link</a> to visit the Talent Database page.<p>',
                $data['sender'],
                $data['performer'],
                $data['link']);
            
            $email->addContent("text/html", $content);            
                
            // $push->sendPushNotification(null, 'cms_to_user', $user, $content);
            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function sendPerformance($user, $data)
    {
        try {
            // $push = new NotificationManagementController();
            $email = new Mail();

            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Your appointment time to audition ' . $data['audition_title'] . 'is update');
            $email->addTo($user->email);
            $content = sprintf('Your appointment time to audition ' . $data['audition_title'] . ' is update to ' . $data['slot_time']);

            $email->addContent("text/html", $content);
            // $push->sendPushNotification(null, 'cms_to_user', $user, $content);
            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function sendForgotPasswordLink($remember_token, $user)
    {
        try {
            $setLink = '';
            if (isset($user->details->type) && $user->details->type == 1) {
                $setLink = env('CASTER_PASSWORD_LINK') . "/password/reset-password/" . $remember_token;
            } else if (isset($user->details->type) && $user->details->type == 2) {
                $setLink = env('PERFORMER_PASSWORD_LINK') . "/password/reset-password/" . $remember_token;
            } else {
                return response()->json(['data' => trans('messages.email_not_found')], 404);
            }

            $email = new Mail();

            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Reset Your Password');
            $email->addTo($user->email);
            $email->addContent("text/html", "Hello " . $user->email . "<br/><br/>Reset Your Password: <strong><a href='" . $setLink . "' style='font-size: 14px;line-height: normal;color: #ffffff;font-weight: bold;display: inline-block;background-color: #f8a33e;text-decoration: none;padding: 2px 20px;'>Click Here</a></strong><br/><br/>Please, change the password now.");

            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function sendInvitedCaster($password, $emailTo, $data)
    {   
        try {
            $email = new Mail();
            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Gig2Gig+ Caster Invitation');
            $email->addTo($emailTo);
            $email->addContent("text/html", "Congratulations! You are invited by ". $data['name'] ." for accessing Gig2Gig+.<br />Your new password is: <strong>" .
                $password . "</strong><br/><a href='https://casting.gig2gig.com/login' target='_blank'>Click here to Log In</a><br />Please, change the password right away after logging in.");

            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return true;
            } else {
                $this->log->error($response->body() . " " . $response->statusCode());
                return false;
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return false;
        }
    }
}
