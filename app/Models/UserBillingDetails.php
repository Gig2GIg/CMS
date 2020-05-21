<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBillingDetails extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'city',
        'birth',
        'gender',
        'state',
        'country',
        'zip',
    ];

    public function users(){
        $this->belongsTo(User::class);
    }
}
