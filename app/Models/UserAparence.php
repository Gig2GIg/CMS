<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAparence extends Model
{
    protected $fillable = [
        'height',
        'weight',
        'hair',
        'eyes',
        'race',
        'user_id',
        'personal_flare',
        'gender_pronouns'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
