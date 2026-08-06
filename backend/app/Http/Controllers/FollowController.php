<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FollowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FollowController extends Controller
{
    protected $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }

    public function toggle(Request $request, User $user): JsonResponse
    {
        $result = $this->followService->toggle($request->user(), $user);
        return response()->json($result);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $suggestions = $this->followService->getSuggestions($request->user());
        return response()->json($suggestions);
    }
}
