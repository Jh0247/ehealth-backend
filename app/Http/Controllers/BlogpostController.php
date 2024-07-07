<?php

namespace App\Http\Controllers;

use App\Models\Blogpost;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\CreateBlogpostValidationStrategy;
use App\Services\Validation\UpdateBlogpostValidationStrategy;
use App\Services\Validation\UpdateBlogpostStatusValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * BlogpostController handles all operations related to blogposts.
 */
class BlogpostController extends Controller
{
    /**
     * @var ValidatorContext
     */
    protected $validatorContext;

    public function __construct()
    {
        $this->validatorContext = new ValidatorContext();
    }

    /**
     * Create a new blogpost.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBlogpost(Request $request)
    {
        $this->validatorContext->addStrategy(new CreateBlogpostValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['errors' => $validationResult['errors']], 422);
        }

        $user = $request->user();

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('blog_banners', 's3');
            Storage::url($bannerPath);
            $bannerPath = env('APP_S3_URL'). $bannerPath;
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
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBlogposts(Request $request)
    {
        $query = Blogpost::orderBy('created_at', 'desc');
    
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    
        $blogposts = $query->paginate(50);
    
        return response()->json($blogposts);
    }

    /**
     * Update the status of a blogpost.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBlogpostStatus(Request $request, $id)
    {
        $this->validatorContext->addStrategy(new UpdateBlogpostStatusValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['errors' => $validationResult['errors']], 422);
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
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBlogpost(Request $request, $id)
    {
        $user = $request->user();
        $blogpost = $user->blogposts()->find($id);

        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }

        if ($blogpost->banner) {
            $bannerPath = str_replace(env('APP_S3_URL'), '', $blogpost->banner);
            Storage::disk('s3')->delete($bannerPath);
        }

        $blogpost->delete();

        return response()->json(['message' => 'Blogpost deleted successfully']);
    }

    /**
     * Search for blogposts by title.
     *
     * @param Request $request
     * @param string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchBlogpostByName(Request $request, $name)
    {
        $blogposts = Blogpost::where('title', 'like', '%' . $name . '%')->paginate(50);

        return response()->json($blogposts);
    }

    /**
     * Get a specific blogpost by its ID.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBlogpost(Request $request, $id)
    {
        $this->validatorContext->addStrategy(new UpdateBlogpostValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);
    
        if ($validationResult['errors']) {
            return response()->json(['errors' => $validationResult['errors']], 422);
        }
    
        $user = $request->user();
        $blogpost = $user->blogposts()->find($id);
    
        if (!$blogpost) {
            return response()->json(['error' => 'Blogpost not found'], 404);
        }
    
        if ($request->hasFile('banner')) {
            if ($blogpost->banner) {
                $bannerPath = str_replace(env('APP_S3_URL'), '', $blogpost->banner);
                Storage::disk('s3')->delete($bannerPath);
            }
    
            $bannerPath = $request->file('banner')->store('blog_banners', 's3');
            Storage::url($bannerPath);
            $bannerPath = env('APP_S3_URL'). $bannerPath;
    
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
     *
     * @param Request $request
     * @param string $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBlogpostsByStatus(Request $request, $status)
    {
        $blogposts = Blogpost::where('status', $status)->orderBy('created_at', 'desc')->paginate(50);

        return response()->json($blogposts);
    }

    /**
     * Get blogposts of the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserBlogposts(Request $request)
    {
        $user = $request->user();
        $blogposts = $user->blogposts()->orderBy('created_at', 'desc')->paginate(50);

        return response()->json($blogposts);
    }
}
