<?php

namespace App\Http\Controllers;

use App\Facades\UserFacade;
use App\Models\User;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\UserProfileUpdateValidationStrategy;
use App\Services\Validation\UserStatusUpdateValidationStrategy;
use App\Services\Validation\UserPasswordUpdateValidationStrategy;
use App\Services\Validation\UserSearchByIcnoValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * UserController handles all operations related to user management.
 */
class UserController extends Controller
{
    /**
     * @var ValidatorContext
     */
    protected $profileUpdateValidatorContext;

    /**
     * @var ValidatorContext
     */
    protected $statusUpdateValidatorContext;

    /**
     * @var ValidatorContext
     */
    protected $passwordUpdateValidatorContext;

    /**
     * @var ValidatorContext
     */
    protected $searchByIcnoValidatorContext;

    /**
     * @var UserFacade
     */
    protected $userFacade;

    /**
     * UserController constructor.
     *
     * @param UserFacade $userFacade
     */
    public function __construct(UserFacade $userFacade)
    {
        $this->profileUpdateValidatorContext = new ValidatorContext();
        $this->profileUpdateValidatorContext->addStrategy(new UserProfileUpdateValidationStrategy());

        $this->statusUpdateValidatorContext = new ValidatorContext();
        $this->statusUpdateValidatorContext->addStrategy(new UserStatusUpdateValidationStrategy());

        $this->passwordUpdateValidatorContext = new ValidatorContext();
        $this->passwordUpdateValidatorContext->addStrategy(new UserPasswordUpdateValidationStrategy());

        $this->searchByIcnoValidatorContext = new ValidatorContext();
        $this->searchByIcnoValidatorContext->addStrategy(new UserSearchByIcnoValidationStrategy());

        $this->userFacade = $userFacade;
    }

    /**
     * Update the profile of the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $validationResult = $this->profileUpdateValidatorContext->validate($request);
    
        if ($validationResult['errors']) {
            return response()->json($validationResult['errors'], 422);
        }
    
        $user = $request->user();
    
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('contact')) {
            $user->contact = $request->contact;
        }
    
        if ($request->hasFile('profile_img')) {
            Log::info('Profile image file detected');
    
            $s3 = new S3Client([
                'region'  => env('AWS_DEFAULT_REGION', 'ap-southeast-2'),
                'version' => 'latest',
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);
            Log::info(env('AWS_ACCESS_KEY_ID'));
    
            $file = $request->file('profile_img');
            $filePath = $file->getPathname();
            $fileName = 'profile_images/' . uniqid() . '_' . $file->getClientOriginalName();
            $bucket = env('AWS_BUCKET');
    
            try {
                $result = $s3->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $fileName,
                    'SourceFile' => $filePath,
                    'ACL'    => 'public-read',
                ]);
    
                $imageUrl = $result['ObjectURL'];
                Log::info('Image URL: ' . $imageUrl);
    
                // Delete the old image if it exists
                if ($user->profile_img) {
                    $oldImageKey = str_replace(env('APP_S3_URL') . '/', '', $user->profile_img);
                    $s3->deleteObject([
                        'Bucket' => $bucket,
                        'Key'    => $oldImageKey,
                    ]);
                }
    
                $user->profile_img = $imageUrl;
            } catch (AwsException $e) {
                Log::error('AWS S3 Upload Error: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to upload image'], 500);
            }
        }
    
        $user->save();
        $user->load('organization');
    
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Get users by role and organization ID.
     *
     * @param int $organizationId
     * @param string $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersByRoleAndOrganization($organizationId, $role)
    {
        $users = User::where('organization_id', $organizationId)
            ->where('user_role', $role)
            ->get();

        return response()->json($users);
    }

    /**
     * Get users by organization ID excluding the first admin and the authenticated user.
     *
     * @param Request $request
     * @param int $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersByOrganization(Request $request, $organizationId)
    {
        $firstUser = User::where('organization_id', $organizationId)
            ->where('user_role', 'admin')
            ->orderBy('created_at', 'asc')
            ->first();

        $authenticatedUser = $request->user();

        $users = User::where('organization_id', $organizationId)
            ->where('id', '!=', $firstUser ? $firstUser->id : null)
            ->where('id', '!=', $authenticatedUser->id)
            ->get();

        return response()->json($users);
    }

    /**
     * Update the status of a specific user by user ID.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserStatus(Request $request, $id)
    {
        $validationResult = $this->statusUpdateValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['errors' => implode(' ', $validationResult['errors'])], 422);
        }

        $this->userFacade->updateUser($id, ['status' => $request->status]);

        return response()->json(['message' => 'User status updated successfully']);
    }

    /**
     * Search for users by IC number.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUserByIcno(Request $request)
    {
        $validationResult = $this->searchByIcnoValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json($validationResult['errors'], 422);
        }

        $icno = $request->input('icno');
        $users = User::where('icno', 'like', '%' . $icno . '%')
            ->where('user_role', 'user')
            ->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found with the provided IC number'], 404);
        }

        return response()->json($users);
    }

    /**
     * Update the password of the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $validationResult = $this->passwordUpdateValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json($validationResult['errors'], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 403);
        }

        $this->userFacade->updateUser($user->id, ['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password updated successfully'], 200);
    }
}
