<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\UserProfileUpdateValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $profileUpdateValidatorContext;

    public function __construct()
    {
        $this->profileUpdateValidatorContext = new ValidatorContext();
        $this->profileUpdateValidatorContext->addStrategy(new UserProfileUpdateValidationStrategy());
    }

    public function updateProfile(Request $request)
    {
        $validationResult = $this->profileUpdateValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json($validationResult['errors'], 422);
        }

        // Get the authenticated user
        $user = $request->user;

        // Update user details
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('contact')) {
            $user->contact = $request->contact;
        }

        // Handle profile image upload
        if ($request->hasFile('profile_img')) {
            // Delete the old profile image if it exists
            if ($user->profile_img) {
                Storage::disk('s3')->delete($user->profile_img);
            }

            // Upload the new profile image
            $imagePath = $request->file('profile_img')->store('profile_images', 's3');

            // Save the path to the user's profile_img field
            Storage::url($imagePath);
            
           // string builder to add bucket name to url
            $user->profile_img = 'http://localhost:9000/ehealth/'.$imagePath;
        }

        // Save the updated user details
        $user->save();

        $user->load('organization');

        // Return a response
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function getUsersByRoleAndOrganization($organizationId, $role)
    {
        $users = User::where('organization_id', $organizationId)
            ->where('user_role', $role)
            ->get();

        return response()->json($users);
    }

    public function getUsersByOrganization(Request $request, $organizationId)
    {
        // Find the first user who requested the collaboration
        $firstUser = User::where('organization_id', $organizationId)
            ->where('user_role', 'admin')
            ->orderBy('created_at', 'asc')
            ->first();
    
        // Get the authenticated user
        $authenticatedUser = $request->user();
    
        // Get all users in the organization excluding the first user and the authenticated user
        $users = User::where('organization_id', $organizationId)
            ->where('id', '!=', $firstUser ? $firstUser->id : null)
            ->where('id', '!=', $authenticatedUser->id)
            ->get();
    
        return response()->json($users);
    }

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

    public function updatePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user;

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
