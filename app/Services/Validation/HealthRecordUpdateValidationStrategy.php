<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class HealthRecordUpdateValidationStrategy
 *
 * @package App\Services\Validation
 */
class HealthRecordUpdateValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the health record update fields in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'health_condition' => 'nullable|string|in:Healthy,Good,Fair,Poor',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergic' => 'nullable|array',
            'diseases' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
