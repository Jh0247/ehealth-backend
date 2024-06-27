<?php

namespace App\Repositories\User;

use App\Models\User;

/**
 * Class UserRepository
 *
 * @package App\Repositories\User
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function find($id)
    {
        return User::find($id);
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data)
    {
        return User::create($data);
    }

    /**
     * Update a user by ID.
     *
     * @param int $id
     * @param array $data
     * @return User|null
     */
    public function update($id, array $data)
    {
        $user = User::find($id);
        if ($user) {
            $user->update($data);
            return $user;
        }
        return null;
    }

    /**
     * Delete a user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return $user;
        }
        return null;
    }
}
