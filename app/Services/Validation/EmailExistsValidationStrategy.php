<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use App\Models\User;

/**
 * Class EmailExistsValidationStrategy
 *
 * @package App\Services\Validation
 */
class EmailExistsValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate if the email exists.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ['errors' => 'No account found.'];
        }

        return ['errors' => null];
    }
}
