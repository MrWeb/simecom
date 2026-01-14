<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VideoSegment extends Model
{
    protected $fillable = [
        'slug',
        'category',
        'file_path',
        'duration_seconds',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration_seconds' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getS3Url(): string
    {
        return Storage::disk('s3')->url($this->file_path);
    }

    public function getLocalTempPath(): string
    {
        return storage_path("app/temp/{$this->slug}.mp4");
    }
}
