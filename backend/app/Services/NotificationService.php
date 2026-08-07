<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;

class NotificationService
{
    public function getRecent(User $user, int $limit = 30): array
    {
        $userId = $user->id;
        $notifications = [];

        Like::with(['user', 'post'])
            ->whereHas('post', fn ($query) => $query->where('user_id', $userId))
            ->where('user_id', '!=', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (Like $like) use (&$notifications) {
                $notifications[] = $this->format($like->user, 'like', $like->post, $like->created_at);
            });

        Comment::with(['user', 'post'])
            ->whereHas('post', fn ($query) => $query->where('user_id', $userId))
            ->where('user_id', '!=', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (Comment $comment) use (&$notifications) {
                $notifications[] = $this->format(
                    $comment->user,
                    'comment',
                    $comment->post,
                    $comment->created_at,
                    $comment->body
                );
            });

        Follow::with('follower')
            ->where('followed_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (Follow $follow) use (&$notifications) {
                $notifications[] = $this->format($follow->follower, 'follow', null, $follow->created_at);
            });

        usort($notifications, fn (array $a, array $b) => $b['created_at'] <=> $a['created_at']);

        return array_slice($notifications, 0, $limit);
    }

    protected function format(User $actor, string $type, ?Post $post, $createdAt, ?string $commentBody = null): array
    {
        return [
            'id' => $type . '_' . ($post?->id ?? $actor->id) . '_' . $createdAt->timestamp,
            'type' => $type,
            'user' => [
                'id' => $actor->id,
                'username' => $actor->username,
                'name' => $actor->name,
                'avatar_url' => $actor->avatar_url,
            ],
            'post' => $post ? [
                'id' => $post->id,
                'image_url' => $post->image_url,
                'caption' => $post->caption,
            ] : null,
            'comment_body' => $commentBody,
            'created_at' => $createdAt->toISOString(),
        ];
    }
}
