<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $guarded = [];

    public function path()
    {
        return '/articles/' . $this->id;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
