<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function search(string $query, ?User $authUser = null, int $perPage = 15): LengthAwarePaginator
    {
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->withCount('followers')
            ->paginate($perPage);

        return $this->withFollowingState($users, $authUser);
    }

    public function getAllPaginated(?User $authUser = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->withFollowingState(
            User::withCount('followers')->paginate($perPage),
            $authUser
        );
    }

    protected function withFollowingState(LengthAwarePaginator $paginator, ?User $authUser): LengthAwarePaginator
    {
        if (! $authUser) {
            return $paginator;
        }

        $followingIds = $authUser->following()->pluck('users.id')->toArray();

        $paginator->getCollection()->transform(function (User $user) use ($followingIds) {
            $user->is_following = in_array($user->id, $followingIds);

            return $user;
        });

        return $paginator;
    }
}