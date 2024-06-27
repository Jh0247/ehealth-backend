<?php

namespace App\Services\UserStatusValidation;

use Illuminate\Http\JsonResponse;

/**
 * Class NoAccountFoundStrategy
 *
 * @package App\Services\UserStatusValidation
 */
class NoAccountFoundStrategy implements UserStatusStrategyInterface
{
    /**
     * Validate the user's status.
     *
     * @param $user
     * @return JsonResponse|null
     */
    public function validate($user)
    {
        if (!$user) {
            return response()->json(['error' => 'No account found.'], 401);
        }
        return null;
    }
}
