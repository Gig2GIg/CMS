<?php

namespace App\Http\Controllers\Utils;

use App\Http\Repositories\UserRepository;

use App\Http\Controllers\Controller;
use App\Models\User;
use Stripe\Stripe;

class StripeManagementController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->log = new LogManger();
    }

    public function setSubscription(Array $data)
    {
        $result = false;
        try {
            $this->connect();
            $userRepo = new UserRepository(new User());
            $dataUser = $userRepo->find($data['id']);
            $res = $dataUser->newSubscription(env('STRIPE_PLAN_SUBS'), $this->getPlan($data['pricing_type']))->create($data['stripeToken']);
            if (isset($res->id)) {
                $result = true;
            }
            return $result;


        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return $result;
        }
    }

    public function connect()
    {
        $connect = new Stripe();
        $res = $connect->setApiKey(env('STRIPE_SECRET'));
        return $res;
    }

    public function changeSubscription(Array $data)
    {
        $result = false;
        $user = new User();
        try {
            $this->connect();

            $dataUser = $user->find($data['id']);
            $res = $dataUser->subscription(env('STRIPE_PLAN_SUBS'))->swap($this->getPlan($data['pricing_type']));
            if (isset($res->id)) {
                $result = true;
            }
            return $result;


        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return $result;
        }
    }

    public function cancelSubscription(Array $data)
    {
        $result = false;
        $user = new User();
        try {
            $this->connect();
            $dataUser = $user->find($data['id']);
            $res = $dataUser->subscription(env('STRIPE_PLAN_SUBS'))->cancel();
            if (isset($res->id)) {
                $result = true;
            }
            return $result;
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return $result;
        }
    }

    public function getPlan($data){
        $element = null;
        $plans= [
            '0'=>env('STRIPE_PLAN0'),
            '2'=>env('STRIPE_PLAN1'),
            '3'=>env('STRIPE_PLAN2'),
        ];

        if(array_key_exists($data,$plans)){
            $element = $plans[$data];
        }
        return $element;
    }
}
