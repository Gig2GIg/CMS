<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempUserImportedList extends Model
{
	public $timestamps = false;

    protected $fillable = [
        'email',
        'password'
    ];
}
