<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(Request $request): JsonResponse
    {
        $posts = $this->postService->getFeed($request->user());
        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:2200',
        ]);

        $post = $this->postService->create(
            $request->user(),
            $validated,
            $request->file('image')
        );

        return response()->json($post, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->getById($id);
        $post->is_liked = $this->postService->isLikedByUser($post, $request->user());

        return response()->json($post);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        try {
            $this->postService->delete($request->user(), $post);
            return response()->json(['message' => 'Post deleted successfully']);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }
    }

    public function userPosts(Request $request, int $userId): JsonResponse
    {
        $posts = $this->postService->getUserPosts($userId);
        return response()->json($posts);
    }
}
