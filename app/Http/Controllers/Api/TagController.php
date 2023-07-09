<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $tags = Tag::all();
            return response()->json($tags);

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

            $validateTag = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|unique:tags,name'
                ]
            );

            if ($validateTag->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateTag->errors()
                ], 403);
            }

            $tag = Tag::create(['name' => $request->name]);

            return response()->json($tag);

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

            $tag = Tag::find($id);

            if (!$tag) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tag not found'
                ], 404);
            }

            return response()->json($tag);

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

            $validateTag = Validator::make(
                $request->all(),
                ['name' => 'required|string|unique:tags,name']
            );

            if ($validateTag->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateTag->errors()
                ], 403);
            }

            $tag = Tag::find($id);

            if (!$tag) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tag not found'
                ], 404);
            }

            $tag['name'] = $request->name;
            $tag->save();

            return response()->json($tag);

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

            $tag = Tag::find($id);

            if (!$tag) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tag not found'
                ], 404);
            }

            $tag->delete();

            return response()->json($tag);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
