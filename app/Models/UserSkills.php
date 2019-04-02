<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSkills extends Model
{
    protected $fillable = [
        'user_id',
        'skills_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function skills(){
        return $this->belongsTo(Skills::class);
    }
}
