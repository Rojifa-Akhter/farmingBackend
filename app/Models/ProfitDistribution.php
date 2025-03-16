<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitDistribution extends Model
{
    protected $guarded =['id'];
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
