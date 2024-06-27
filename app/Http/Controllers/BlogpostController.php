<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blogpost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogpostController extends Controller
{
    /**
     * Create a new blogpost.
     */
    public function createBlogpost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'banner' => 'nullable|image',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => implode(' ', $errors)], 422);
        }

        $user = $request->user();

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('blog_banners', 's3');
            Storage::url($bannerPath);
            $bannerPath = env('APP_S3_URL') . '/ehealth/' . $bannerPath;
        }

        $blogpost = $user->blogposts()->create([
            'title' => $request->title,
            'content' => $request->content,
            'banner' => $bannerPath,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Blogpost created successfully',
            'blogpost' => $blogpost
        ], 201);
    }

    /**
     * Get all blogposts with optional status filtering.
     */
    public function getAllBlogposts(Request $request)
    {
        $query = Blogpost::orderBy('created_at', 'desc');
    
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    
        $blogposts = $query->paginate(10);
    
        return response()->json($blogposts);
    }

    /**
     * Update the status of a blogpost.
     */
    public function updateBlogpostStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => implode(' ', $errors)], 422);
        }

        $blogpost = Blogpost::find($id);

        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }

        $blogpost->update(['status' => $request->status]);

        return response()->json(['message' => 'Blogpost status updated successfully']);
    }

    /**
     * Delete a specific blogpost by its ID.
     */
    public function deleteBlogpost(Request $request, $id)
    {
        $user = $request->user();
        $blogpost = $user->blogposts()->find($id);

        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }

        if ($blogpost->banner) {
            $bannerPath = str_replace(env('APP_S3_URL') . '/ehealth/', '', $blogpost->banner);
            Storage::disk('s3')->delete($bannerPath);
        }

        $blogpost->delete();

        return response()->json(['message' => 'Blogpost deleted successfully']);
    }

    /**
     * Search for blogposts by title.
     */
    public function searchBlogpostByName(Request $request, $name)
    {
        $blogposts = Blogpost::where('title', 'like', '%' . $name . '%')->paginate(10);

        return response()->json($blogposts);
    }

    /**
     * Get a specific blogpost by its ID.
     */
    public function getSpecificBlogpost(Request $request, $id)
    {
        $blogpost = Blogpost::with('user')->find($id);

        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }

        return response()->json($blogpost);
    }

    /**
     * Update a specific blogpost.
     */
    public function updateBlogpost(Request $request, $id)
    {
        $data = $request->all();
    
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'banner' => 'nullable|image',
            'status' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => implode(' ', $errors)], 422);
        }
    
        $user = $request->user();
        $blogpost = $user->blogposts()->find($id);
    
        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }
    
        if ($request->hasFile('banner')) {
            if ($blogpost->banner) {
                $bannerPath = str_replace(env('APP_S3_URL') . '/ehealth/', '', $blogpost->banner);
                Storage::disk('s3')->delete($bannerPath);
            }
    
            $bannerPath = $request->file('banner')->store('blog_banners', 's3');
            Storage::url($bannerPath);
            $bannerPath = env('APP_S3_URL') . '/ehealth/' . $bannerPath;
    
            $blogpost->banner = $bannerPath;
        }
    
        $blogpost->title = $request->input('title');
        $blogpost->content = $request->input('content');
        $blogpost->status = $request->input('status');
    
        $blogpost->save();
    
        return response()->json([
            'message' => 'Blogpost updated successfully',
            'blogpost' => $blogpost
        ]);
    }

    /**
     * Get blogposts by status with pagination.
     */
    public function getBlogpostsByStatus(Request $request, $status)
    {
        $blogposts = Blogpost::where('status', $status)->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($blogposts);
    }

    /**
     * Get blogposts of the authenticated user.
     */
    public function getUserBlogposts(Request $request)
    {
        $user = $request->user();
        $blogposts = $user->blogposts()->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($blogposts);
    }
}