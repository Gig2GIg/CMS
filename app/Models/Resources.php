<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resources extends Model
{
    protected $fillable =['url','thumbnail','type','name', 'shareable'];
    
    public function resources(){
        return $this->morphTo(
        
        );
    }
}
