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
        'status',
        'user_id'
    ];

    public function media(){
        return $this->morphOne(Resources::class,'resource');
    }

    public function resources(){
        return $this->hasMany(Resources::class,'resource_id');
    }

    public function roles(){
        return $this->hasMany(Roles::class);
    }

    public function appointment(){
        return $this->hasOne(Appointments::class);
    }

    public function dates(){
        return $this->morphOne(Dates::class,'date');
    }

    public function datesall(){
        return $this->hasMany(Dates::class,'date_id');
    }

    public function contributors(){
        return $this->hasMany(AuditionContributors::class);
    }
}
