<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSlots extends Model
{
    protected $fillable = [
        'user_id','slots_id','status','auditions_id','roles_id','favorite'
    ];

    public function slot(){
        return $this->belongsTo(Slots::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function audition(){
        return $this->belongsTo(Auditions::class);
    }
}
