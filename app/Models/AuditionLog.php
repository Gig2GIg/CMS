<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditionLog extends Model
{
	protected $fillable = [
        'audition_id',
        'key',
        'old_value',
        'new_value',
        'edited_by'
    ];

    public function user(){
        $this->belongsTo(User::class, 'edited_by');
    }

    public function audition(){
        return$this->belongsTo(Auditions::class);
    }
}
