<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = ['name', 'stripe_plan', 'header', 'allowed_performers', 'description', 'amount', 'type', 'user_type', 'is_discounted', 'is_custom', 'is_active'];

    protected $dates = [
        'created_at', 'updated_at',
    ];
}
