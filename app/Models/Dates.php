<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dates extends Model
{
    protected $fillable =[
        'type',
        'to',
        'from',
    ];

    public function auditions(){
        $this->belongsTo(Auditions::class);
    }

    public function dates(){
        return $this->morphTo();
    }
}
