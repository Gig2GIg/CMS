<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;

use App\Http\Resources\Cms\SubcribersPaymentsResource;
use App\Http\Controllers\Utils\StripeManagementController;

use App\Http\Exceptions\NotFoundException;
use App\Models\User;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Stripe\Stripe;

class SubcribersController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function payments(Request $request)
    {


        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $stripeResult = \Stripe\Invoice::all(["limit" => 3]);

        return response()->json(['data' => $stripeResult], 200);

    }
}
