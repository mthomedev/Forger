<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = ['user_id', 'image_path', 'caption'];

    protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveStorageUrl($this->image_path);
    }

    protected function resolveStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $url = Storage::url($path);

        return str_starts_with($url, 'http') ? $url : url($url);
    }
}
