<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class BookAppointmentValidationStrategy
 *
 * @package App\Services\Validation
 */
class BookAppointmentValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for booking an appointment.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
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
