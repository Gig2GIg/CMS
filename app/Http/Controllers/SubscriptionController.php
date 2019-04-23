<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\StripeManagementController;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function managementSubscription(Request $request)
    {
        try {
            $userRepo = new UserRepository(new User());
            $userData = $userRepo->find($this->getUserLogging());
            $details = $userData->details;
            if ($details->subscription === '1') {
                $result = $this->createSubscription($request);
                if ($result) {
                    $userData->details()->update(['subscription' => $request->plan]);
                    $dataResponse = ['data' => 'Subscription Create'];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => 'Subscription Error'];
                    $code = 406;
                }
            } else {
                $result = $this->updateSubscription($request);

                if ($result) {
                    $userData->details()->update(['subscription' => $request->plan]);
                    $dataResponse = ['data' => 'Subscription Update'];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => 'Subscription Updated Error'];
                    $code = 406;
                }
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 406);
        }
    }

    public function createSubscription(Request $request)
    {
        $stripe = new StripeManagementController();
        $data = [
            'id' => $this->getUserLogging(),
            'pricing_type' => $request->plan,
            'stripeToken' => $request->token_stripe
        ];
        return $stripe->setSubscription($data);
    }

    public function updateSubscription(Request $request)
    {
        $stripe = new StripeManagementController();
        $data = [
            'id' => $this->getUserLogging(),
            'pricing_type' => $request->plan,
        ];
        return $stripe->changeSubscription($data);

    }

    public function cancelSubscription()
    {
        try {
            $stripe = new StripeManagementController();
            $data = [
                'id' => $this->getUserLogging(),
            ];

            $result = $stripe->cancelSubscription($data);
            if ($result) {
                $userRepo = new UserRepository(new User());
                $userData = $userRepo->find($this->getUserLogging());
                $details = $userData->details;
                $userData->details()->update(['subscription' => '1']);
                $dataResponse = ['data' => 'Subscription Cancel'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Subscription cancel Error'];
                $code = 406;
            }


            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 406);
        }
    }
}
