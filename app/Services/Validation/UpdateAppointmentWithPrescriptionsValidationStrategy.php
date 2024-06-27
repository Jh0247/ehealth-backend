<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UpdateAppointmentWithPrescriptionsValidationStrategy
 *
 * @package App\Services\Validation
 */
class UpdateAppointmentWithPrescriptionsValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for updating an appointment with prescriptions.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'duration' => 'nullable|integer',
            'note' => 'nullable|string',
            'prescriptions' => 'required|array|min:1',
            'prescriptions.*.medication_id' => 'required|exists:medications,id',
            'prescriptions.*.dosage' => 'required|string',
            'prescriptions.*.frequency' => 'required|string',
            'prescriptions.*.duration' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
