<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditionContributors extends Model
{
    protected $fillable =[
        'user_id',
        'auditions_id',
        'status'
    ];
    public function auditions(){
        return $this->belongsTo(Auditions::class);
    }

    public function user(){
        return $this->hasOne(User::class);
    }

}
