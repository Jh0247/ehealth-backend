<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserPasswordUpdateValidationStrategy
 *
 * @package App\Services\Validation
 */
class UserPasswordUpdateValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the user password update fields in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
