<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;

use App\Http\Resources\Cms\SubcribersPaymentsResource;
use App\Http\Controllers\Utils\StripeManagementController;

use App\Http\Exceptions\NotFoundException;
use App\Models\User;

use Laravel\Cashier\Subscription;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Stripe\Stripe;

use App\Http\Controllers\Utils\LogManger;


class SubcribersController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt');
        $this->log = new LogManger();
    }

    public function payments(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $stripeResult = \Stripe\Invoice::all(["limit" => 100]);
        return response()->json(['data' => $stripeResult], 200);
    }

    public function unsubscribe(Request $request)
    {
        try {

            $stripe = new StripeManagementController();

            $data = [
                'id' => $request->id,
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

    public function plans()
    {
        $stripe = new StripeManagementController();

        $plans = collect($stripe->getStripePlans()->data);
        $subscriptions = Subscription::with('user.details')->get();

        $subscriptions->each(function ($subscription) use ($plans) {
            $plan = $plans->where('id', $subscription->stripe_plan)->first();
            $subscription->amount = number_format(($plan ? $plan->amount : 0) / 100, 2);
        });

        return $subscriptions;
    }
}
