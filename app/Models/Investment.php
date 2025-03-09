<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $guarded=['id'];

    // Define relationships
    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }
}
