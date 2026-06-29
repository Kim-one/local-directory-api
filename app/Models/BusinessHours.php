<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessHours extends Model
{
    protected $fillable = ['business_id', 'day', 'open_time', 'close_time', 'is_closed'];

    protected $casts = ['is_closed' => 'boolean'];
}
