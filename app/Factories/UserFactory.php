<?php

namespace App\Factories;

use App\Models\User;

abstract class UserFactory
{
    /**
     * Factory method to create a user.
     *
     * @param array $data
     * @return \App\Models\User
     */
    abstract public function createUser(array $data): User;
}
