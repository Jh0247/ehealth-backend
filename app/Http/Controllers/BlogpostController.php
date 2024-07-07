<?php
// hi
namespace App\Http\Controllers;

use App\Models\Blogpost;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\CreateBlogpostValidationStrategy;
use App\Services\Validation\UpdateBlogpostValidationStrategy;
use App\Services\Validation\UpdateBlogpostStatusValidationStrategy;
use Illuminate\Http\Request;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

/**
 * BlogpostController handles all operations related to blogposts.
 */
class BlogpostController extends Controller
{
    /**
     * @var ValidatorContext
     */
    protected $validatorContext;

    /**
     * @var S3Client
     */
    protected $s3;

    public function __construct()
    {
        $this->validatorContext = new ValidatorContext();
        $this->s3 = new S3Client([
            'region'  => env('AWS_DEFAULT_REGION', 'ap-southeast-2'),
            'version' => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
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
            try {
                $file = $request->file('banner');
                $filePath = $file->getPathname();
                $fileName = 'blog_banners/' . uniqid() . '_' . $file->getClientOriginalName();
                $bucket = env('AWS_BUCKET');

                $result = $this->s3->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $fileName,
                    'SourceFile' => $filePath,
                    'ACL'    => 'public-read',
                ]);

                $bannerPath = $result['ObjectURL'];
            } catch (AwsException $e) {
                Log::error('AWS S3 Upload Error: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to upload banner'], 500);
            }
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
            $this->s3->deleteObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $bannerPath,
            ]);
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
            try {
                if ($blogpost->banner) {
                    $bannerPath = str_replace(env('APP_S3_URL'), '', $blogpost->banner);
                    $this->s3->deleteObject([
                        'Bucket' => env('AWS_BUCKET'),
                        'Key'    => $bannerPath,
                    ]);
                }
    
                $file = $request->file('banner');
                $filePath = $file->getPathname();
                $fileName = 'blog_banners/' . uniqid() . '_' . $file->getClientOriginalName();
                $bucket = env('AWS_BUCKET');

                $result = $this->s3->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $fileName,
                    'SourceFile' => $filePath,
                    'ACL'    => 'public-read',
                ]);

                $bannerPath = $result['ObjectURL'];
                $blogpost->banner = $bannerPath;
            } catch (AwsException $e) {
                Log::error('AWS S3 Upload Error: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to upload banner'], 500);
            }
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
