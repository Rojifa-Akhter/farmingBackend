<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $guarded = ['id'];
    // Relationship: Category belongs to a Farmer (User)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getIconAttribute($icon)
    {
        return asset('uploads/product_icons/' . ($icon ?? null));
    }
}
