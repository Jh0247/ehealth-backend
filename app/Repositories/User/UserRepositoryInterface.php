<?php

namespace App\Repositories\User;

/**
 * Interface UserRepositoryInterface
 *
 * @package App\Repositories\User
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function find($id);

    /**
     * Create a new user.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function create(array $data);

    /**
     * Update a user by ID.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\User|null
     */
    public function update($id, array $data);

    /**
     * Delete a user by ID.
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function delete($id);

    /**
     * Find users by organization ID.
     *
     * @param int $organizationId
     * @return \Illuminate\Support\Collection
     */
    public function findByOrganization($organizationId);
}
