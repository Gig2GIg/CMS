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
        $this->belongsTo(Auditions::class);
    }

    public function userdetails(){
        $this->hasOne(UserDetails::class,'user_id','user_id');
    }
}
