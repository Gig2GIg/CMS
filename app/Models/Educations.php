<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Educations extends Model
{
    protected $fillable =[
        'school',
        'degree',
        'instructor',
        'location',
        'year',
        'user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
