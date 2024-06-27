<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserSearchByIcnoValidationStrategy
 *
 * @package App\Services\Validation
 */
class UserSearchByIcnoValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the IC number search fields in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'icno' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
