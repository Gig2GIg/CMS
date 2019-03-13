<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resources extends Model
{
    protected $fillable =['url'];
    public function resources(){
        return $this->morphTo();
    }
}
