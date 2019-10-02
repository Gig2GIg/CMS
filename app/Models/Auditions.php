<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Notifications\Notification;
class Auditions extends Model
{
    protected $fillable = [
        'title',
        'description',
        'url',
        'personal_information',
        'phone',
        'email',
        'other_info',
        'additional_info',
        'union',
        'contract',
        'production',
        'status',
        'user_id',
        'banned'
    ];

    public function media(){
        return $this->morphOne(Resources::class,'resource');
    }

    public function resources(){
        return $this->hasMany(Resources::class,'resource_id');
    }

    public function roles(){
        return $this->hasMany(Roles::class);
    }

    public function appointment(){
        return $this->hasOne(Appointments::class);
    }

    public function dates(){
        return $this->morphOne(Dates::class,'date');
    }

    public function datesall(){
        return $this->hasMany(Dates::class,'date_id');
    }

    public function contributors(){
        return $this->hasMany(AuditionContributors::class);
    }

    //NOTIFICATIONS
    public function notifications(){
        return $this->hasMany(Notification::class,'notificationable_id');
    }

    public function userauditions(){
        return $this->hasMany(UserAuditions::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function recommendations_marketplaces(){
        return $this->hasMany(Recommendations::class, 'audition_id');
    }
}
