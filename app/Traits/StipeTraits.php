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

    // /* retrieve stripe customer */
    // public function retrieveCustomer($customer = '')
    // {
    //     return $customer = Stripe::customers()->find($customer);
    // }

    // public function updateDefaultSrc($customer = '', $card = '')
    // {
    //     return $customer = Stripe::customers()->update($customer, [
    //         'default_source' => $card,
    //     ]);
    // }

    // /* create card token */
    // public function createCardToken($card = array())
    // {
    //     $token = Stripe::tokens()->create([
    //         'card' => [
    //             'number'    => $card['number'],
    //             'exp_month' => $card['exp_month'],
    //             'cvc'       => $card['cvc'],
    //             'exp_year'  => $card['exp_year']
    //         ],
    //     ]);

    //     return $token['id'];
    // }

    // /* create card from token */
    // public function createCardFromToken($customer = '', $token = '')
    // {
    //     $card = Stripe::cards()->create($customer, $token);

    //     return $card;
    // }

    // /* create card from number and cvv */
    // public function createCard($customer = '', $card = array())
    // {
    //     $token = $this->createCardToken($card);
        
    //     $card = Stripe::cards()->create($customer, $token);

    //     return $card;
    // }

    // /* get customer cards */
    // public function getCustomerCards($customer = '')
    // {
    //     $cards = Stripe::cards()->all($customer);

    //     return $cards;
    // }

    // /* delete card */
    // public function deleteCard($customer = '', $card = '')
    // {
    //     $card = Stripe::cards()->delete($customer, $card);

    //     return $card;
    // }

    // /* create charge // amount in doller not in cent */
    // public function createCharge($customer = '', $amount = 0, $source = NULL)
    // {
    //     $charge = Stripe::charges()->create([
    //         'customer' => $customer,
    //         'currency' => env('STRIPE_CURRENCY', 'USD'),
    //         'amount'   => $amount,
    //         'source'   => $source // optional
    //     ]);
        
    //     return $charge['id'];
    // }

    // /* retrieve charge */
    // public function retrieveCharge($charge = '')
    // {
    //     return $charge = Stripe::charges()->find($charge);
    // }

    // /* refund charge // amount and reason optional */
    // public function refundCharge($charge = '', $amount = 0, $reason = '')
    // {
    //     try {
    //         $refund = Stripe::refunds()->create(
    //             $charge, $amount ? $amount : NULL,            
    //             ['reason' => $reason ? $reason : 'requested_by_customer']
    //         );
    
    //         return $refund['id'];
    //     } catch (\Exception $e) {
    //         throw new \Exception($e->getErrorType());
    //     }
    // }
}