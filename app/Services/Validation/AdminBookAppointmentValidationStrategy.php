<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class AdminBookAppointmentValidationStrategy
 *
 * @package App\Services\Validation
 */
class AdminBookAppointmentValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for booking an appointment as an admin.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'appointment_datetime' => 'required|date',
            'type' => 'required|string',
            'purpose' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
