<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditions extends Model
{
    protected $fillable = [
        'title',
        'date',
        'time',
        'location',
        'description',
        'url',
        'union',
        'contract',
        'production',
        'status'
    ];

    public function image(){
        return $this->morphOne(Resources::class,'resource');
    }

    public function roles(){
        return $this->hasMany(Roles::class);
    }
}
