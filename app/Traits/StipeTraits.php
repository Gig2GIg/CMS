<?php

namespace App\Traits;
use Laravel\Cashier\Cashier;
use App\Models\User;

trait StipeTraits
{
    /* create stripe customer */
    public function createCustomer($user)
    {
        $customer = $user->createAsStripeCustomer(['email' => $user->email]);

        return $customer['id'];
    }

    public function updateDefaultSrc($user, $card = '')
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $customer = \Stripe\Customer::createSource($user->stripe_id, [
            'source' => $card['id'],
        ]);

        return $user->updateDefaultPaymentMethod($customer['id']);
    }

    public function deletePaymentMethod($user)
    {
        return $user->paymentMethods()->first()->delete();
    }

    /* create card token */
    public function createCardToken($card = array())
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $token = \Stripe\Token::create([
            'card' => [
                'number'    => $card['number'],
                'exp_month' => $card['exp_month'],
                'cvc'       => $card['cvc'],
                'exp_year'  => $card['exp_year'],
                'name'      => isset($card['name_on_card']) ? $card['name_on_card'] : NULL,
            ],
        ]);

        return $token;
    }

    public function subscribeUser($user, $planData = array(), $paymentMethod)
    {
        return $user->newSubscription($planData['stripe_plan_name'], $planData['stripe_plan_id'])->create($paymentMethod->paymentMethod, []);
    }

    public function listAllPlans()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        return \Stripe\Plan::all(['product' => env('SUBS_PROD')]);
    }

    public function getSubscriptionDetails($user)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        return \Stripe\Subscription::all(['customer' => $user->stripe_id, 'status' => 'active']);        
    }

    public function getUpcomingInvoice($user)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        return \Stripe\Invoice::upcoming(["customer" => $user->stripe_id, 'subscription' => $user->subscriptions()->first()->stripe_id]);
    }
}