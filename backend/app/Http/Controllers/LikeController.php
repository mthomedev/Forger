<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\LikeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    public function toggle(Request $request, Post $post): JsonResponse
    {
        $result = $this->likeService->toggle($request->user(), $post);
        return response()->json($result);
    }
}
