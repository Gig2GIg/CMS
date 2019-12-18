<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Performers extends Model
{
    //
    protected $fillable =[
        'performer_id',
        'director_id',
        'uuid'
    ];

    public function details(){
        return $this->hasOne(UserDetails::class,'user_id','performer_id');
    }

}
