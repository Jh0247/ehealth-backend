<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class CreateMedicationValidationStrategy
 *
 * @package App\Services\Validation
 */
class CreateMedicationValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for creating a new medication.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredient' => 'required|string',
            'form' => 'required|string|max:255',
            'usage' => 'required|string',
            'strength' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
