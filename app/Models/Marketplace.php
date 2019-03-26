<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $fillable = [
        'address',
        'email',
        'services',
        'title',
        'phone_number',
        'marketplace_category_id'
    ];

    public function category(){
        return $this->belongsTo(MarketplaceCategory::class);
    }

    public function image(){
        return $this->morphOne(Resources::class,'resource');
    }

}