<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSocialLink extends Model
{
    protected $fillable = ['business_id', 'platform', 'url'];
}
