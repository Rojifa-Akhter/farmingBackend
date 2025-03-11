<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded=['id'];
    public function buyer()
    {
       return $this->belongsTo(User::class,'user_id');
    }
}
