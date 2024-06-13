<?php

namespace App\Http\Controllers;

use App\Services\Validation\ValidatorContext;
use App\Services\Validation\UserProfileUpdateValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        // Return a response
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
