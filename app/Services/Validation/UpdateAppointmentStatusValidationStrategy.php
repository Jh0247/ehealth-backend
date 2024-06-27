<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UpdateAppointmentStatusValidationStrategy
 *
 * @package App\Services\Validation
 */
class UpdateAppointmentStatusValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for updating appointment status.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
