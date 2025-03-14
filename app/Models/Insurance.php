<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $guarded=['id'];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
