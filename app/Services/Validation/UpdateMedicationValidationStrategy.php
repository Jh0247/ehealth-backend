<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UpdateMedicationValidationStrategy
 *
 * @package App\Services\Validation
 */
class UpdateMedicationValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for updating a medication.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'ingredient' => 'sometimes|required|string',
            'form' => 'sometimes|required|string|max:255',
            'usage' => 'sometimes|required|string',
            'strength' => 'sometimes|required|string|max:255',
            'manufacturer' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
