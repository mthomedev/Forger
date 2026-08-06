<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->withCount('followers')
            ->paginate($perPage);
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return User::withCount('followers')
            ->paginate($perPage);
    }
}
