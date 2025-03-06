<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    protected $guarded=['id'];

    public function farmer()
    {
       return $this->belongsTo(User::class,'farmer_id');
    }
}
