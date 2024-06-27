<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserStatusUpdateValidationStrategy
 *
 * @package App\Services\Validation
 */
class UserStatusUpdateValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the user status update fields in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,active,terminated',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
