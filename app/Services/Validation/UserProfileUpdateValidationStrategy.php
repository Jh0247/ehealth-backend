<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserProfileUpdateValidationStrategy implements ValidationStrategyInterface
{
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . auth()->id(),
            'contact' => 'string|max:15',
            'profile_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ],[
            'profile_img' => 'The profile image may not be greater than 2 MB.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
