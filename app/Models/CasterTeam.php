<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasterTeam extends Model
{
    protected $fillable = [
        'admin_id',
        'member_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at' 
    ];

    public function admins(){
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function members(){
        return $this->belongsTo(User::class, 'member_id');
    }
}
