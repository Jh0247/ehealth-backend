<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class OrganizationIdValidationStrategy
 *
 * @package App\Services\Validation
 */
class OrganizationIdValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the organization ID in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
