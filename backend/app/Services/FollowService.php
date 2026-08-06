<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FollowService
{
    public function toggle(User $follower, User $target): array
    {
        if ($follower->id === $target->id) {
            return [
                'following' => false,
                'followers_count' => $target->followers()->count()
            ];
        }

        $following = false;
        
        $exists = DB::table('follows')
            ->where('follower_id', $follower->id)
            ->where('followed_id', $target->id)
            ->exists();

        if ($exists) {
            DB::table('follows')
                ->where('follower_id', $follower->id)
                ->where('followed_id', $target->id)
                ->delete();
        } else {
            DB::table('follows')->insert([
                'follower_id' => $follower->id,
                'followed_id' => $target->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $following = true;
        }

        return [
            'following' => $following,
            'followers_count' => $target->followers()->count(),
        ];
    }

    public function getSuggestions(User $user, int $limit = 5): Collection
    {
        $followingIds = $user->following()->pluck('users.id')->toArray();
        $followingIds[] = $user->id;

        return User::whereNotIn('id', $followingIds)
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->limit($limit)
            ->get();
    }
}
