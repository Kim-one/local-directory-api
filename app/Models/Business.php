<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BusinessSocialLink;
use App\Models\BusinessHours;
use App\Models\Review;

class Business extends Model
{
    //
    protected $fillable = [
        'user_id',
        'slug',
        'businessName',
        'slogan',
        'category',
        'city',
        'parish',
        'streetAddress',
        'description',
        'about',
        'phone',
        'email',
        'website',
        'featured',
        'verified',
        'rating',
        'reviewCount'
    ];

    protected $casts = [
        'rating' => 'float',
        'featured' => 'boolean',
        'verified' => 'boolean',
        'reviewCount' => 'integer'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(BusinessImage::class);
    }

    public function heroImage()
    {
        return $this->hasOne(BusinessImage::class)->where('type', 'hero');
    }

    public function galleryImages()
    {
        return $this->hasMany(BusinessImage::class)->where('type', 'gallery');
    }

    public function socialLinks()
    {
        return $this->hasMany(BusinessSocialLink::class);
    }

    public function hours()
    {
        return $this->hasMany(BusinessHours::class)->orderByRaw("
            CASE day
                WHEN 'monday'    THEN 1
                WHEN 'tuesday'   THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday'  THEN 4
                WHEN 'friday'    THEN 5
                WHEN 'saturday'  THEN 6
                WHEN 'sunday'    THEN 7
            END
        ");
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }
}
