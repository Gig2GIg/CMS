<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $fillable = [
        'production_type',
        'project_name',
        'start_date',
        'end_date',
        'user_id'
    ];
}
