<?php

namespace Tests\Unit;

use App\Http\Controllers\Utils\StripeManagementController;
use App\Models\User;
use Tests\TestCase;

class StripeManagementUnitTest extends TestCase
{
    public function test_stripe_subscription()
    {
        $user = factory(User::class)->create();
        $connect = new StripeManagementController();
        $req = [];
        $req['pricing_type'] = '2';
        $req['stripeToken'] = 'tok_visa';
        $req['id'] = $user->id;
        $test = $connect->setSubscription($req);
        $this->assertTrue($test);

    }

    public function test_stripe_subscription_error()
    {
        $user = factory(User::class)->create();
        $connect = new StripeManagementController();
        $req = [];
        $req['pricing_type'] = 'plan';
        $req['stripeToken'] = 'tok_visa';
        $req['id'] = $user->id;
        $test = $connect->setSubscription($req);
        $this->assertFalse($test);

    }


    public function test_stripe_subscription_change()
    {
        $user = factory(User::class)->create();
        $connect = new StripeManagementController();
        $req = [];
        $req['pricing_type'] = '2';
        $req['stripeToken'] = 'tok_visa';
        $req['id'] = $user->id;
        $test = $connect->setSubscription($req);
        $req['pricing_type'] = '3';
        $update = $connect->changeSubscription($req);

        $this->assertTrue($update);

    }

    public function test_stripe_subscription_change_error()
    {
        $user = factory(User::class)->create();
        $connect = new StripeManagementController();
        $req = [];
        $req['pricing_type'] = '2';
        $req['stripeToken'] = 'tok_visa';
        $req['id'] = $user->id;
        $test = $connect->setSubscription($req);
        $req['pricing_type'] = '2';
        $update = $connect->changeSubscription($req);

        $this->assertTrue($update);

    }

    public function test_stripe_subscription_cancel()
    {
        $user = factory(User::class)->create();
        $connect = new StripeManagementController();
        $req = [];
        $req['pricing_type'] = '2';
        $req['stripeToken'] = 'tok_visa';
        $req['id'] = $user->id;
        $test = $connect->setSubscription($req);
        $delete = $connect->cancelSubscription($req);
        $this->assertTrue($delete);

    }

    public function test_stripe_subscription_cancel_error()
    {
        $user = factory(User::class)->create();
        $connect = new StripeManagementController();
        $req = [];
        $req['pricing_type'] = '2';
        $req['stripeToken'] = 'tok_visa';
        $req['id'] = $user->id;
        $test = $connect->setSubscription($req);
        $req['id'] = $user->id;
        $delete = $connect->cancelSubscription([]);
        $this->assertFalse($delete);

    }


}
