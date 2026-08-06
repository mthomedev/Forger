<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function index(Request $request, Post $post): JsonResponse
    {
        $comments = $this->commentService->getForPost($post->id);
        return response()->json($comments);
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2200',
        ]);

        $comment = $this->commentService->create(
            $request->user(),
            $post,
            $validated['body']
        );

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        try {
            $this->commentService->delete($request->user(), $comment);
            return response()->json(['message' => 'Comment deleted successfully']);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }
    }
}
