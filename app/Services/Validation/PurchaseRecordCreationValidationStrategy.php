<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class PurchaseRecordCreationValidationStrategy
 *
 * @package App\Services\Validation
 */
class PurchaseRecordCreationValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the purchase record creation fields in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'pharmacist_id' => 'required|exists:users,id',
            'medication_id' => 'required|exists:medications,id',
            'date_purchase' => 'required|date',
            'quantity' => 'required|integer',
            'total_payment' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
