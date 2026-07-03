<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedPlace extends Model
{
    protected $fillable = ['business_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
