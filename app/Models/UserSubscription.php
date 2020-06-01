<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = ['user_id', 'name', 'stripe_id', 'stripe_status', 'stripe_plan', 'product_id', 'original_transaction', 'current_transaction', 'quntity', 'purchased_at', 'trial_ends_at', 'ends_at', 'purchase_platform'];

    protected $dates = [
        'trial_ends_at', 'ends_at', 'purchased_at', 'created_at', 'updated_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
