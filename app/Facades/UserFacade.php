<?php

namespace App\Facades;


use App\Repositories\User\UserRepositoryInterface;
use App\Factories\AdminUserFactory;
use App\Factories\NormalUserFactory;
use App\Factories\StaffUserFactory;
use App\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserFacade
{
    protected $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data, $userRole)
    {
        if (!isset($data['organization_id'])) {
            $data['organization_id'] = 1;
        };
        $userFactory = $this->getUserFactory($userRole, $data['organization_id']);
        $user = $userFactory->createUser($data);
        return $user;
    }

    protected function getUserFactory($userRole, $organizationId): UserFactory
    {
        if ($organizationId == 1) {
            if ($userRole == 'admin') {
                return new AdminUserFactory();
            } else {
                return new NormalUserFactory();
            }
        } else {
            return new StaffUserFactory();
        }
    }

    public function updateUser($userId, array $data)
    {
        return $this->userRepository->update($userId, $data);
    }

    public function deleteUser($userId)
    {
        return $this->userRepository->delete($userId);
    }

    public function findUser($userId)
    {
        return $this->userRepository->find($userId);
    }
}
