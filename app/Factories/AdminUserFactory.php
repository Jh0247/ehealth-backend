<?php

namespace App\Factories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class AdminUserFactory
 *
 * @package App\Factories
 */
class AdminUserFactory extends UserFactory
{
    /**
     * Create a new admin user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return User::create([
            'organization_id' => 1,
            'name' => $data['name'],
            'email' => $data['email'],
            'icno' => $data['icno'],
            'contact' => $data['contact'],
            'password' => Hash::make($data['password']),
            'user_role' => 'e-admin',
            'status' => 'active',
        ]);
    }
}
