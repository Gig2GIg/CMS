<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceCategory extends Model
{
    protected $fillable =[
        'name',
        'description',
    ];

    public function marketplaces(){
        return $this->hasMany(Marketplace::class);
    }
}
