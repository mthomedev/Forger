<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function show(Request $request)
    {
        $user = $this->profileService->getProfileWithStats($request->user());
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users')->ignore($user->id),
            ],
            'bio' => 'nullable|string|max:1000',
        ]);

        $updatedUser = $this->profileService->updateProfile($user, $validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $updatedUser
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $result = $this->profileService->uploadAvatar(
            $request->user(),
            $request->file('avatar')
        );

        return response()->json([
            'message' => 'Avatar updated successfully',
            'avatar_url' => $result['avatar_url'],
            'user' => $result['user']
        ]);
    }

    public function showUser($username)
    {
        $user = $this->profileService->getPublicProfile($username);
        return response()->json($user);
    }
}
