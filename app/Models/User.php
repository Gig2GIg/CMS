<?php

namespace App\Models;

use App\Models\Notifications\NotificationHistory;
use App\Models\Notifications\NotificationSettingUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'pushkey', 'password_reset_token', 'is_active', 'is_premium', 'invited_by', 'card_brand', 'card_last_four', 'trial_ends_at', 'stripe_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function details()
    {
        return $this->hasOne(UserDetails::class);
    }

    public function billingDetails()
    {
        return $this->hasOne(UserBillingDetails::class);
    }

    public function userSubscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function memberunions()
    {
        return $this->hasMany(UserUnionMembers::class);
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function image()
    {
        return $this->morphOne(Resources::class, 'resource');
    }

    public function contributors()
    {
        return $this->belongsTo(AuditionContributors::class);
    }

    public function skills()
    {
        return $this->hasMany(UserSkills::class);
    }

    //NOTIFICATIONS RELATIONSHIPS
    public function notification_settings()
    {
        return $this->hasMany(
            NotificationSettingUser::class
        );
    }

    public function notification_settings_on()
    {
        return $this->hasMany(
            NotificationSettingUser::class
        )->where('status', 'on');
    }

    public function notification_history()
    {
        return $this->hasMany(NotificationHistory::class)->orderBy('created_at');
    }

    public function educations()
    {
        return $this->hasMany(Educations::class);
    }
    public function credits()
    {
        return $this->hasMany(Credits::class);
    }

    public function aparence()
    {
        return $this->hasOne(UserAparence::class);
    }

    public function userAuditions()
    {
        return $this->hasMany(UserAuditions::class);
    }

    public function pushkey()
    {
        return $this->hasMany(UserPushKeys::class);
    }

    public function performerShareCode()
    {
        return $this->hasOne(Performers::class, 'performer_id');
    }

}
