<?php

namespace App\Factories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class NormalUserFactory
 *
 * @package App\Factories
 */
class NormalUserFactory extends UserFactory
{
    /**
     * Create a new normal user.
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
            'user_role' => 'user',
            'status' => 'active',
        ]);
    }
}
