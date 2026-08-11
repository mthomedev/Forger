<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = $request->query('search');
        
        if ($query) {
            $users = $this->userService->search($query, $request->user());
        } else {
            $users = $this->userService->getAllPaginated($request->user());
        }

        return response()->json($users);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $user->loadCount(['posts', 'followers', 'following']);
        
        $isFollowing = false;
        if ($request->user()) {
            $isFollowing = $request->user()->following()->where('users.id', $user->id)->exists();
        }
        
        $user->is_following = $isFollowing;

        return response()->json($user);
    }
}
