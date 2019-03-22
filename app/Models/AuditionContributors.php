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

    public function user(){
        $this->hasOne(User::class);
    }

}
