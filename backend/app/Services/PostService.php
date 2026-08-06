<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function create(User $user, array $data, UploadedFile $image): Post
    {
        $imagePath = $image->store('posts');

        return $user->posts()->create([
            'caption' => $data['caption'] ?? null,
            'image_path' => $imagePath,
        ]);
    }

    public function getById(int $id): Post
    {
        return Post::with(['user', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->findOrFail($id);
    }

    public function getFeed(User $user, int $perPage = 10): LengthAwarePaginator
    {
        $followingIds = $user->following()->pluck('users.id')->toArray();
        $userIds = array_merge([$user->id], $followingIds);

        // Fallback: if user follows nobody, show all posts
        $query = Post::with(['user'])
            ->withCount(['likes', 'comments']);

        if (count($followingIds) > 0) {
            $query->whereIn('user_id', $userIds);
        }

        $paginator = $query->latest()->paginate($perPage);

        $paginator->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked = $this->isLikedByUser($post, $user);
            return $post;
        });

        return $paginator;
    }

    public function getUserPosts(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        $paginator = Post::where('user_id', $userId)
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate($perPage);

        return $paginator;
    }

    public function delete(User $user, Post $post): void
    {
        if ($post->user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to delete this post.');
        }

        if ($post->image_path) {
            Storage::delete($post->image_path);
        }

        $post->delete();
    }

    public function isLikedByUser(Post $post, User $user): bool
    {
        return $post->likes()->where('user_id', $user->id)->exists();
    }
}
