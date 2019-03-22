<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $fillable = [
        'address',
        'email',
        'services',
        'phone_number'
    ];
    public function category(){
        return $this->belongsTo(MarketplaceCategory::class);
    }
}
