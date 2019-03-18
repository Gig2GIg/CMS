<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $fillable = [
        'name',
        'description',
        'audition_id',
        'cover',
    ];
    public function image(){
        return $this->morphOne(Resources::class,'resource');
    }

    public function auditon(){
        return $this->belongsTo(Auditions::class);
    }
}
