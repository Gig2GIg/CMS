<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditionContributors extends Model
{
    protected $fillable =[
        'email',
        'audition_id'
    ];
    public function auditions(){
        $this->belongsTo(Auditions::class);
    }
}
