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
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function monitorings()
    {
        return $this->hasMany(FarmMonitoring::class);
    }
    public function getImageAttribute($image)
    {
        $images = json_decode($image, true);

        if (is_array($images) && count($images) > 0) {
            return array_map(function ($img) {
                return asset('uploads/farm_images/' . $img);
            }, $images);
        }
        return [];
    }

    public function getVideoAttribute($video)
    {
        $videos = json_decode($video, true);
        if (is_array($videos) && count($videos) > 0) {
            return array_map(function ($vdo) {
                return asset('uploads/farm_videos/' . $vdo);
            }, $videos);
        }
        return [];
    }
    public function marketplaces()
    {
        return $this->hasMany(Marketplace::class);
    }
}
