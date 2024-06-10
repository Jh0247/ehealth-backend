<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use App\Models\User;

class EmailExistsValidationStrategy implements ValidationStrategyInterface
{
    public function validate(Request $request): array
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ['errors' => ['email' => ['No account found.']]];
        }

        return ['errors' => null];
    }
}
