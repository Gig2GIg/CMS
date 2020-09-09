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
            $content = $data['name'] . ' has requested an appointment for ' . $data['audition'] . '.<br/>Click here to visit <a href="' . $data['url'] . '">' . $data['url'] . '</a><br/><br/>Why am I receiving this email?<br/>Gig2Gig is the latest in Casting Technology.<br/>You have most likely been added as "representation" for one or more of your clients who are currently using this platform.<br/>Due to the recent Covid19 pandemic, Gig2Gig is committed to making auditions as safe as possible. This platform allows for 100% paperless casting, audition management, social distance grouping and crowd management, and up to the minute updates and notifications.<br/><br/>To learn more about Gig2Gig, please visit <a href="www.Gig2Gig.com">www.Gig2Gig.com</a>.';
            // $content = sprintf("%s has requested an appointment for %s",
            //     $data['name'],
            //     $data['audition'],
            //     $data['url']);
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
            $email->setSubject('Gig2Gig Casting Team Member');
            $email->addTo($emailTo);
            $email->addContent("text/html", "Congratulations!<br/><br/>". $data['name'] ." has invited you to join their Gig2Gig+ team.<br/><br/>To access your account login with this email address.<br/><br/>Your temporary password is : <strong>" . $password . "</strong><br/><br/>You'll have the ability to change your password during your first login.<br/><br/><a href='" . env('CASTER_BASE_URL') . "' target='_blank'>Click Here to Login</a><br/>Thanks,<br/><br/>The Gig2Gig Team");

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

    public function sendUpgradeMail($emailTo, $data)
    {   
        try {
            $email = new Mail();
            $email->setFrom(env('SUPPORT_EMAIL'));
            $email->setSubject('Gig2Gig Caster Subscription Upgradation');
            $email->addTo($emailTo);
            $email->addContent("text/html", "Congratulations! " . $data['name'] . ",<br />You are upgraded to new plan in which you can ". $data['new_sub'] ." Previous plan was having limit of " . $data['old_sub'] . " Performer Profiles in the Talent Database.<br />You will be charged <strong>$" . $data['new_amount'] . "</strong> for Upgraded Subscription on next billing cycle dated <strong>". $data['next_billing_date'] ." UTC</strong>.");

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
