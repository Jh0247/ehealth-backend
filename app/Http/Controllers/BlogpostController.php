<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blogpost;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogpostController extends Controller
{
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
            $bannerPath = 'http://localhost:9000/ehealth/' . $bannerPath;
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

    public function getAllBlogposts(Request $request)
    {
        $query = Blogpost::orderBy('created_at', 'desc');
    
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    
        $blogposts = $query->paginate(10);
    
        return response()->json($blogposts);
    }

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

    public function deleteBlogpost(Request $request, $id)
    {
        $user = $request->user();
        $blogpost = $user->blogposts()->find($id);

        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }

        // Delete the banner image from S3 if it exists
        if ($blogpost->banner) {
            $bannerPath = str_replace('http://localhost:9000/ehealth/', '', $blogpost->banner);
            Storage::disk('s3')->delete($bannerPath);
        }

        $blogpost->delete();

        return response()->json(['message' => 'Blogpost deleted successfully']);
    }

    public function searchBlogpostByName(Request $request, $name)
    {
        $blogposts = Blogpost::where('title', 'like', '%' . $name . '%')->paginate(10);

        return response()->json($blogposts);
    }

    public function getSpecificBlogpost(Request $request, $id)
    {
        $blogpost = Blogpost::with('user')->find($id);

        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }

        return response()->json($blogpost);
    }

    public function updateBlogpost(Request $request, $id)
    {
        // Access the request data
        $data = $request->all();
    
        // Validate the request data
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'banner' => 'nullable|image',
            'status' => 'required|string',
        ]);
    
        // Handle validation errors
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => implode(' ', $errors)], 422);
        }
    
        // Get the authenticated user
        $user = $request->user();
        $blogpost = $user->blogposts()->find($id);
    
        // Check if the blogpost exists
        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }
    
        // Handle banner image upload
        if ($request->hasFile('banner')) {
            // Delete the old banner image if it exists
            if ($blogpost->banner) {
                $bannerPath = str_replace('http://localhost:9000/ehealth/', '', $blogpost->banner);
                Storage::disk('s3')->delete($bannerPath);
            }
    
            // Upload the new banner image
            $bannerPath = $request->file('banner')->store('blog_banners', 's3');
            Storage::url($bannerPath);
            $bannerPath = 'http://localhost:9000/ehealth/' . $bannerPath;
    
            // Update the banner path
            $blogpost->banner = $bannerPath;
        }
    
        // Update other fields
        $blogpost->title = $request->input('title');
        $blogpost->content = $request->input('content');
        $blogpost->status = $request->input('status');
    
        // Save the updated blogpost
        $blogpost->save();
    
        // Return a response
        return response()->json([
            'message' => 'Blogpost updated successfully',
            'blogpost' => $blogpost
        ]);
    }

    public function getBlogpostsByStatus(Request $request, $status)
    {
        $blogposts = Blogpost::where('status', $status)->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($blogposts);
    }

    public function getUserBlogposts(Request $request)
    {
        $user = $request->user();
        $blogposts = $user->blogposts()->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($blogposts);
    }
}
