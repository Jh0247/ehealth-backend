<?php

namespace App\Services\UserStatusValidation;

interface UserStatusStrategyInterface
{
    public function validate($user);
}
