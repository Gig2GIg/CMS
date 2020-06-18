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

    //webhook functions for Stripe
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        if ($user = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            $user->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                $subscription->markAsCancelled();
            });

            $this->updateUserPremiumStatus($user, 0);
        }

        $this->log->info('STRIPE WEBHOOK:: Subscription cancelled for User ID '. $user->id);

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionUpdated(array $payload)
    {
        if ($user = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            $data = $payload['data']['object'];

            $user->subscriptions->filter(function (Subscription $subscription) use ($data) {
                return $subscription->stripe_id === $data['id'];
            })->each(function (Subscription $subscription) use ($data) {
                if (isset($data['status']) && $data['status'] === 'incomplete_expired') {
                    $subscription->delete();
                    $this->updateUserPremiumStatus($user, 0);

                    return;
                }

                // Quantity...
                if (isset($data['quantity'])) {
                    $subscription->quantity = $data['quantity'];
                }

                // Plan...
                if (isset($data['plan']['id'])) {
                    $subscription->stripe_plan = $data['plan']['id'];
                }

                // Trial ending date...
                if (isset($data['trial_end'])) {
                    $trial_ends = Carbon::createFromTimestamp($data['trial_end']);

                    if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trial_ends)) {
                        $subscription->trial_ends_at = $trial_ends;
                    }
                }

                // Cancellation date...
                if (isset($data['cancel_at_period_end'])) {
                    if ($data['cancel_at_period_end']) {
                        $subscription->ends_at = $subscription->onTrial()
                            ? $subscription->trial_ends_at
                            : Carbon::createFromTimestamp($data['current_period_end']);
                    } else {
                        $subscription->ends_at = null;
                    }
                }

                // Status...
                if (isset($data['status'])) {
                    $subscription->stripe_status = $data['status'];
                    if($data['status'] == 'past_due' || $data['status'] == 'incomplete_expired' || $data['status'] == 'canceled'){
                        $this->updateUserPremiumStatus($user, 0);
                    }else{
                        $this->updateUserPremiumStatus($user, 1);
                    } 
                }

                $subscription->save();
            });
        }

        return $this->successMethod();
    }

    public function handleinvoicePaymentSucceeded(array $payload)
    {
        if ($user = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            $user->subscriptions->each(function ($subscription) use ($payload) {
               
                // Period ending date...
                if (isset($data['period_end'])) {
                    $period_ends = Carbon::createFromTimestamp($data['period_end']);

                    $subscription->ends_at = $period_ends;
                    $subscription->stripe_status = 'active';
                } 

                $subscription->save();
            });

            $this->updateUserPremiumStatus($user, 1);
        }

        $this->log->info('STRIPE WEBHOOK:: Subscription payment suceeded for User ID '. $user->id);

        return $this->successMethod();
    }

    protected function getUserByStripeId($stripeId)
    {
        if ($stripeId === null) {
            return;
        }

        return (new User)->where('stripe_id', $stripeId)->first();
    }

    protected function updateUserPremiumStatus($user = null, $status)
    {
        if ($status === null) {
            return;
        }

        $userRepo = new User;

        $user->update(array('is_premium' => $status));
        $userRepo->where('invited_by', $user->id)->update(array('is_premium' => $status));

        return true;
    }

    protected function successMethod($parameters = [])
    {
        return new Response('Webhook Handled', 200);
    }
}
