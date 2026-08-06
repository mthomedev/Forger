<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Auth\Access\AuthorizationException;

class CommentService
{
    public function getForPost(int $postId): Collection
    {
        return Comment::with('user')
            ->where('post_id', $postId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function create(User $user, Post $post, string $body): Comment
    {
        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'body' => $body,
        ]);

        $comment->load('user');

        return $comment;
    }

    public function delete(User $user, Comment $comment): void
    {
        if ($comment->user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to delete this comment.');
        }

        $comment->delete();
    }
}
