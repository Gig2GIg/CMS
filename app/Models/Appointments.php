<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tests\Unit\SlotTest;

class Appointments extends Model
{
    protected $fillable =[
        'slots',
        'type',
        'length',
        'start',
        'end',
        'auditions_id'
    ];

    public function slots(){
        return $this->hasMany(SlotTest::class);
    }
}
