<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Post;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // 1. Create 10 users
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $users[] = User::create([
                'name' => $faker->name(),
                'username' => $faker->unique()->userName(),
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'bio' => $faker->sentence(),
            ]);
        }

        // 2. Create 3-5 posts per user
        $posts = [];
        foreach ($users as $user) {
            $numPosts = rand(3, 5);
            for ($i = 0; $i < $numPosts; $i++) {
                $randomId = rand(1, 1000);
                $posts[] = Post::create([
                    'user_id' => $user->id,
                    'caption' => $faker->paragraph(2),
                    'image_path' => "https://picsum.photos/seed/{$randomId}/600/600",
                ]);
            }
        }

        // 3. Create random follows (each user follows 3-7 others)
        foreach ($users as $follower) {
            $numFollows = rand(3, 7);
            $targets = collect($users)->where('id', '!=', $follower->id)->random($numFollows);
            
            foreach ($targets as $target) {
                // Skip if already following (avoid unique constraint violation)
                $alreadyFollowing = DB::table('follows')
                    ->where('follower_id', $follower->id)
                    ->where('followed_id', $target->id)
                    ->exists();

                if (!$alreadyFollowing) {
                    DB::table('follows')->insert([
                        'follower_id' => $follower->id,
                        'followed_id' => $target->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 4. Create random likes (each user likes ~30% of posts)
        foreach ($users as $user) {
            $likedPosts = collect($posts)->random(floor(count($posts) * 0.3));
            
            foreach ($likedPosts as $post) {
                DB::table('likes')->insert([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Create 1-3 comments per post from random users
        foreach ($posts as $post) {
            $numComments = rand(1, 3);
            for ($i = 0; $i < $numComments; $i++) {
                $randomUser = $users[array_rand($users)];
                DB::table('comments')->insert([
                    'user_id' => $randomUser->id,
                    'post_id' => $post->id,
                    'body' => $faker->sentence(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
