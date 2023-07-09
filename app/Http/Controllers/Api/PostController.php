<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Post_Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $userPosts = Post::with('tags')
                ->where('user_id', request()
                    ->user()->id)
                ->orderByDesc('pinned')
                ->get();

            return response()->json($userPosts);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validatePost = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:255',
                    'body' => 'required|string',
                    'coverImage' => 'required|image',
                    'pinned' => 'required|boolean',
                    'tags' => 'required|array'
                ]
            );

            if ($validatePost->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validatePost->errors()
                ], 403);
            }


            $coverImagePath = '/storage/' . $request
                ->file('coverImage')
                ->store('users/' . $request->user()->id, 'public');

            $post = Post::create([
                'title' => $request->title,
                'body' => $request->body,
                'coverImage' => $coverImagePath,
                'pinned' => (bool) $request->pinned,
                'user_id' => $request->user()->id
            ]);

            foreach ($request->tags as $tagId) {
                $post_tag = Post_Tag::create([
                    'post_id' => $post->id,
                    'tag_id' => $tagId
                ]);
            }

            return response()->json($post, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $post = Post::with('tags')->find($id);

            if (!$post) {
                return response()->json([
                    'status' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            return response()->json($post);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $validatePost = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:255',
                    'body' => 'required|string',
                    'coverImage' => 'sometimes|nullable|image',
                    'pinned' => 'required|boolean',
                    'tags' => 'required|array'
                ]
            );

            if ($validatePost->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validatePost->errors()
                ], 403);
            }

            $post = POST::with('tags')->where('id', $id)->where('user_id', $request->user()->id)->first();

            if (!$post) {
                return response()->json([
                    'status' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            if ($request->coverImage) {
                $newImageCoverPath = '/storage/' . $request
                    ->file('coverImage')
                    ->store('users/' . $request->user()->id, 'public');
            }

            $post->title = $request->title;
            $post->body = $request->body;
            $post->coverImage = ($request->coverImage !== null) ? $newImageCoverPath : $post->coverImage;
            $post->pinned = $request->pinned;


            $post->tags()->allRelatedIds();
            $post->tags()->sync($request->tags);
            $post->save();

            return response()->json($post);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $post = Post::where('id', $id)->where('user_id', request()->user()->id)->first();

            if (!$post) {
                return response()->json([
                    'status' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            // delete also post tag rows  in the junction table
            // that has relation with current post
            foreach ($post->postTagPivot as $postTagPivot) {
                $postTagPivot->delete();
            }

            $post->delete();

            return response()->json($post);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Summary of getDeletedPosts
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getDeletedPosts()
    {
        try {
            $userDeletedPosts = Post::onlyTrashed()
                ->where('user_id', request()
                    ->user()->id)
                ->orderByDesc('pinned')
                ->get();

            return response()->json($userDeletedPosts);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Summary of restore
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function restore($id)
    {
        try {

            $deletedPost = Post::onlyTrashed()
                ->with('tags')
                ->where('id', $id)
                ->where('user_id', request()->user()->id)
                ->first();

            if (!$deletedPost) {
                return response()->json([
                    'status' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $postTagItems = Post_Tag::onlyTrashed()->where('post_id', $deletedPost->id)->get();

            // restore the rows in the post_tag junction first,
            // then restore The deleted post.
            foreach($postTagItems as $item) {
                $item->restore();
            }

            $deletedPost->restore();

            return response()->json($deletedPost);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
