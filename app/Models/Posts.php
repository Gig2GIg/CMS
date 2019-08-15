<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $fillable =[
        'title',
        'body',
        'created_at',
        'user_id',
        'url_media',
        'type',
        'search_to'
    ];

    public function comments(){
        return $this->hasMany(Comments::class, 'post_id');
    } 
}
