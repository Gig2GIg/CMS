<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTopics extends Model
{
    protected $fillable = ['post_id', 'topic_id'];

    // public function user(){
    //     return $this->belongsTo(User::class);
    // }


}
