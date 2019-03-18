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

    public function media(){
        return $this->hasMany(Resources::class,'resource');
    }

    public function roles(){
        return $this->hasMany(Roles::class);
    }

    public function dates(){
        return $this->hasMany(AuditionsDate::class);
    }

    public function contribuitors(){
        //TODO
    }
}
