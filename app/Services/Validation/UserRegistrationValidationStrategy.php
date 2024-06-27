<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserRegistrationValidationStrategy
 *
 * @package App\Services\Validation
 */
class UserRegistrationValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the user registration fields in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'icno' => 'required|string|between:12,14|unique:users',
            'contact' => 'required|string|between:10,15',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
