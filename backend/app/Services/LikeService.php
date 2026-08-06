<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class LikeService
{
    public function toggle(User $user, Post $post): array
    {
        $liked = false;
        
        $like = $post->likes()->where('user_id', $user->id)->first();
        
        if ($like) {
            $like->delete();
        } else {
            $post->likes()->create([
                'user_id' => $user->id
            ]);
            $liked = true;
        }

        return [
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ];
    }
}
