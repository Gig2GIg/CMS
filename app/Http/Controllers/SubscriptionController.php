<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\StripeManagementController;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\SubsCriptionUserResource;
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

    public function updateSubscriptionForUser(Request $request)
    {
        $stripe = new StripeManagementController();
        $data = [
            'id' => $request->user['id'],
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

    public function setDefaultPlan(Request $request)
    {
        try {
            $stripe = new StripeManagementController();
            $data = [
                'id' => $this->getUserLogging(),
                'pricing_type' => 1,
                'stripeToken' => $request->token_stripe
            ];
            if ($stripe->setSubscription($data)) {
                $userRepo = new UserRepository(new User());
                $userData = $userRepo->find($this->getUserLogging());
                $dataResponse = $userData->defaultCard();
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Add Payment method Error'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 406);
        }
    }

    public function getCardData()
    {
        try {
            $dataUserRepo = new UserRepository(new User());
            $dataUser = $dataUserRepo->find($this->getUserLogging());
            if (isset($dataUser->stripe_id)) {
                $dataResponse = $dataUser->defaultCard();
                $code = 200;
            } else {
                $dataResponse = ['data' => 'not card data'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'ERROR'], 404);
        }
    }

    public function getallSubscription()
    {
        try {
            $dataUserRepo = new UserRepository(new User());
            $dataUser = $dataUserRepo->all();
            $filter = $dataUser->filter(function($item){
                return $item->details->type === '2';
            });

            return SubsCriptionUserResource::collection($filter);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'ERROR'], 404);
        }
    }

    public function updateCardData(Request $request)
    {
        try {
            $dataUserRepo = new UserRepository(new User());
            $dataUser = $dataUserRepo->find($this->getUserLogging());
            if (isset($dataUser->stripe_id)) {
                $cardcode_old = $dataUser->card_last_four;
                $dataUser->updateCard($request->token_card);
                $cardcode_new = $dataUser->card_last_four;
                if ($cardcode_new !== $cardcode_old) {
                    $dataResponse = ['data' => 'card data updated'];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => 'card not data updated'];
                    $code = 404;
                }
            } else {
                $dataResponse = ['data' => 'not card data updated'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'ERROR'], 404);
        }
    }

}

