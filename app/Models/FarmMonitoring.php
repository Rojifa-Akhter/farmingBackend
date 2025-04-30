<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmMonitoring extends Model
{
    protected $guarded=['id'];

    // Relationship with User (Farmer)
    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    // Relationship with Farm
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }
}
