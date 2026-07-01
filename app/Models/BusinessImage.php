<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class BusinessImage extends Model
{
    protected $fillable = ['business_id', 'path', 'type'];

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => rtrim(config('filesystems.disks.s3.url'), '/') . '/' . $this->path,
        );
    }

    protected $appends = ['url'];
}
