<?php

namespace App\Http\Controllers\Utils;

use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stripe\Stripe;

class StripeManagementController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->log = new LogManger();
    }
    public function connect(){
        $connect = new Stripe();
        $res = $connect->setApiKey(env('STRIPE_SECRET'));
        $this->log->info($res);
        return $res;

    }

    public function createUser(){
        $this->connect();

    }


    public function setSubscription(Request $request){
        try{
            $this->connect();
            $userRepo = new UserRepository(new User());
            $dataUser = $userRepo->find($request->id);
            $res =$dataUser->newSubscription(env('STRIPE_PLAN_SUBS'),$request->pricing_type)->create($request->stripeToken);

            $this->log->info($res);
        }catch (\Exception $ex){
            return $ex->getMessage();
        }
    }
}
