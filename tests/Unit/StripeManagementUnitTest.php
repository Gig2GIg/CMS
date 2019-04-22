<?php

namespace Tests\Unit;

use App\Http\Controllers\Utils\StripeManagementController;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StripeManagementUnitTest extends TestCase
{
  public function test_stripe_connect(){
      $connect = new StripeManagementController();
      $connect->connect();
      $req = request();
      $req->input('pricing_type','abc');
      $req->input('stripeToken','sdfsdfdsf');
      $req->input('id','1');
      $test = $connect->setSubscription($req);

      $this->assertEmpty($test);

  }
}
