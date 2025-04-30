<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];
    // Buyer Relationship
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Farmer Relationship
    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    // Product Relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function logistics()
    {
        return $this->hasOne(Logistics::class, 'order_id');
    }

}
