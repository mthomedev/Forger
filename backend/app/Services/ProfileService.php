<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Get user profile with counts (posts, followers, following).
     */
    public function getProfileWithStats(User $user): User
    {
        return $user->loadCount(['posts', 'followers', 'following']);
    }

    /**
     * Update user profile data.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $this->getProfileWithStats($user);
    }

    /**
     * Upload user avatar, delete previous avatar if exists, and update user path.
     */
    public function uploadAvatar(User $user, UploadedFile $file): array
    {
        // Delete old avatar from disk if exists
        if ($user->avatar_path) {
            Storage::delete($user->avatar_path);
        }

        // Store new image in 'avatars' directory
        $path = $file->store('avatars');

        $user->update(['avatar_path' => $path]);

        return [
            'avatar_url' => $user->avatar_url,
            'user' => $this->getProfileWithStats($user)
        ];
    }

    /**
     * Get public user profile by username.
     */
    public function getPublicProfile(string $username): User
    {
        return User::where('username', $username)
            ->withCount(['posts', 'followers', 'following'])
            ->firstOrFail();
    }
}
