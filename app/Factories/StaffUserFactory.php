<?php

namespace App\Factories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffUserFactory extends UserFactory
{
    public function createUser(array $data): User
    {
        return User::create([
            'organization_id' => $data['organization_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'icno' => $data['icno'],
            'contact' => $data['contact'],
            'password' => Hash::make($data['password']),
            'user_role' => $data['user_role'], // admin, nurse, doctor, pharmacist
            'status' => $data['status'],
        ]);
    }
}
