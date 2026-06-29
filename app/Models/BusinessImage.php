<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessImage extends Model
{
    protected $fillable = ['business_id', 'path', 'type'];
}
