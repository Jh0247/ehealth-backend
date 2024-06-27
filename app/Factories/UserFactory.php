<?php

namespace App\Factories;

use App\Models\User;

/**
 * Abstract Class UserFactory
 *
 * @package App\Factories
 */
abstract class UserFactory
{
    /**
     * Factory method to create a user.
     *
     * @param array $data
     * @return User
     */
    abstract public function createUser(array $data): User;
}
