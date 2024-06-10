<?php

namespace App\Services\UserStatusValidation;

class NoAccountFoundStrategy implements UserStatusStrategyInterface
{
    public function validate($user)
    {
        if (!$user) {
            return response()->json(['error' => 'No account found.'], 401);
        }
        return null;
    }
}
