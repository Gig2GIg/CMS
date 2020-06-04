<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = ['name', 'stripe_id', 'allowed_performers', 'description', 'amount', 'type', 'user_type'];

    protected $dates = [
        'created_at', 'updated_at',
    ];
}
