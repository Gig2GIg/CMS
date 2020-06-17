<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\StripeManagementController;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;
use Carbon\Carbon;

class SubsCriptionUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // $data = DB::table('subscriptions')->where('user_id', $this->id)->first();
        return [
            'id' => $this->id,
            'user' => $this->user && $this->user->details ? $this->user->details : NULL,
            'expiration' => Carbon::parse($this->ends_at)->format('Y-m-d H:i:s'),
            'plan' => $this->name,
            'plan_id' => $this->plan_id,
            'stripe_id' => $this->stripe_id,
            'stripe_status' => $this->stripe_status,
            'stripe_plan' => $this->stripe_plan,
            'product_id' => $this->product_id,
            'original_transaction' => $this->original_transaction,
            'current_transaction' => $this->current_transaction,
            'transaction_receipt' => $this->transaction_receipt,
            'quantity' => $this->quantity,
            'trial_ends_at' => Carbon::parse($this->trial_ends_at)->format('Y-m-d H:i:s'),
            'purchase_platform' => $this->purchase_platform,
            'purchased_at' => Carbon::parse($this->purchased_at)->format('Y-m-d H:i:s'),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s')
        ];
    }
}
