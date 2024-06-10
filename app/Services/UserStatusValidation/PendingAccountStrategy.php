<?php

namespace App\Services\UserStatusValidation;

class PendingAccountStrategy implements UserStatusStrategyInterface
{
    public function validate($user)
    {
        if ($user && $user->status === 'pending') {
            return response()->json(['error' => 'This account is currently under review, please wait for 1 to 3 working days.'], 403);
        }
        return null;
    }
}
