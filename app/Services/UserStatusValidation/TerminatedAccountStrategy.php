<?php

namespace App\Services\UserStatusValidation;

class TerminatedAccountStrategy implements UserStatusStrategyInterface
{
    public function validate($user)
    {
        if ($user && $user->status === 'terminated') {
            return response()->json(['error' => 'This account has been terminated, please contact admin for further assistance.'], 403);
        }
        return null;
    }
}
