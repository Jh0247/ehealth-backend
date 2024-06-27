<?php

namespace App\Services\UserStatusValidation;

use Illuminate\Http\JsonResponse;

/**
 * Class TerminatedAccountStrategy
 *
 * @package App\Services\UserStatusValidation
 */
class TerminatedAccountStrategy implements UserStatusStrategyInterface
{
    /**
     * Validate the user's status.
     *
     * @param $user
     * @return JsonResponse|null
     */
    public function validate($user)
    {
        if ($user && $user->status === 'terminated') {
            return response()->json(['error' => 'This account has been terminated, please contact admin for further assistance.'], 403);
        }
        return null;
    }
}
