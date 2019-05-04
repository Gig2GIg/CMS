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
        $plan = new StripeManagementController();

        return [
          'name'=>sprintf('%s %s', $this->details->first_name, $this->details->last_name),
            'plan' =>$this->details->subscription ==1  ? 'FREE': 'PAID',
            'subscription'=>$this->details->subscription !== 1? $data:null//$this->subscriptions()

        ];
    }
}
