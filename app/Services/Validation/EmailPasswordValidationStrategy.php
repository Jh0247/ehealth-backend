<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailPasswordValidationStrategy implements ValidationStrategyInterface
{
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        return ['errors' => null];
    }
}
