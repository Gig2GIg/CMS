<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $fillable = [
        'last_name',
        'first_name',
        'type',
        'address',
        'city',
        'birth',
        'gender',
        'gender_desc',
//        'location',
        'state',
        'user_id',
        'profesion',
        'stage_name',
        'url',
        'country',
        'zip',
        'agency_name',
        'twitter',
        'facebook',
        'instagram',
        'linkedin'
    ];

    protected $casts =[
        'location'=>'json'
    ];

    public function users(){
        $this->belongsTo(User::class);
    }

    public function contributors(){
        $this->belongsTo(AuditionContributors::class);
    }

}
