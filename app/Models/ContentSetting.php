<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentSetting extends Model
{
    protected $fillable = [
        'term_of_use',
        'privacy_policy',
        'app_info',
        'contact_us'
    ];

}
