<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\UserProfileUpdateValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
     * UserController constructor.
     */
    public function __construct()
    {
        $this->profileUpdateValidatorContext = new ValidatorContext();
        $this->profileUpdateValidatorContext->addStrategy(new UserProfileUpdateValidationStrategy());
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
            if ($user->profile_img) {
                Storage::disk('s3')->delete($user->profile_img);
            }

            $imagePath = $request->file('profile_img')->store('profile_images', 's3');
            Storage::url($imagePath);
            $user->profile_img = env('APP_S3_URL') . '/ehealth/' . $imagePath;
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
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,active,terminated',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => implode(' ', $errors)], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->status = $request->status;
        $user->save();

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
        $request->validate([
            'icno' => 'required|string',
        ]);
    
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
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully'], 200);
    }
}