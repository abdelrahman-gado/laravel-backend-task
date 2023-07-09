<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function getStats()
    {
        try {

            $usersCount = User::all()->count();
            $postsCount = Post::all()->count();

            $usersCountWithPosts = Post::all()->groupBy('user_id')->count();
            $usersCountWithoutPosts = $usersCount - $usersCountWithPosts;

            return response()->json([
                'users_count' => $usersCount,
                'posts_count' => $postsCount,
                'users_count_without_posts' => $usersCountWithoutPosts
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}