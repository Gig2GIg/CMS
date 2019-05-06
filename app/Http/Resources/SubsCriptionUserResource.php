<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\StripeManagementController;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;

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
        $data = DB::table('subscriptions')->where('user_id', $this->id)->first();

        return [
            'user' => $this->details,
            'plan' => $this->details->subscription,
            'subscription' => $this->details->subscription !== 1 ? $data : null,
        ];
    }
}
