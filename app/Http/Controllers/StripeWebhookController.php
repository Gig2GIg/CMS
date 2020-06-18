<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\Notification\NotificationSettingUserRepository;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Auth;
use App\Traits\StipeTraits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    use StipeTraits;

    const NOT_FOUND_DATA = "Not found Data";
    protected $log;
    protected $date;

    public function __construct()
    {
        $this->log = new LogManger();
    }

    //webhook function for Stripe
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        $userRepo = new User;
        if ($user = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            $user->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                $subscription->markAsCancelled();
            });

            $user->update(array('is_premium' => 0));
            $userRepo->where('invited_by', $user->id)->update(array('is_premium' => 0));
        }

        $this->log->info('STRIPE WEBHOOK:: Subscription cancelled for User ID '. $user->id);

        return $this->successMethod();
    }

    protected function getUserByStripeId($stripeId)
    {
        if ($stripeId === null) {
            return;
        }

        return (new User)->where('stripe_id', $stripeId)->first();
    }

    protected function successMethod($parameters = [])
    {
        return new Response('Webhook Handled', 200);
    }
}
