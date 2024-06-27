<?php

namespace App\Services\UserStatusValidation;

/**
 * Interface UserStatusStrategyInterface
 *
 * @package App\Services\UserStatusValidation
 */
interface UserStatusStrategyInterface
{
    /**
     * Validate the user's status.
     *
     * @param $user
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function validate($user);
}
