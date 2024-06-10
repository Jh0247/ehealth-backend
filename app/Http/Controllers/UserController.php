<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . Auth::id(),
            'contact' => 'string|max:15',
            'profile_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Update user details
        // find design pattern
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
            $user->profile_img = env('APP_URL').':9000/ehealth/'.$imagePath;
        }

        // Save the updated user details
        User::find($user->id)->save();

        // Return a response
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
