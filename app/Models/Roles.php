<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $fillable = [
        'name',
        'description',
        'auditions_id',
    ];
    public function image(){
        return $this->morphOne(Resources::class,'resource');
    }

    public function auditons(){
        return $this->belongsTo(Auditions::class);
    }
}
