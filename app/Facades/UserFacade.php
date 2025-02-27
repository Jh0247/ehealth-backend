<?php

namespace App\Facades;

use App\Repositories\User\UserRepositoryInterface;
use App\Factories\AdminUserFactory;
use App\Factories\NormalUserFactory;
use App\Factories\StaffUserFactory;
use App\Factories\UserFactory;
use App\Models\User;
use App\Services\UserStatusValidation\NoAccountFoundStrategy;
use App\Services\UserStatusValidation\PendingAccountStrategy;
use App\Services\UserStatusValidation\TerminatedAccountStrategy;
use App\Services\Validation\ValidatorContext;

/**
 * Class UserFacade
 *
 * @package App\Facades
 */
class UserFacade
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var array The array of user status validation strategies.
     */
    protected $statusValidatorContext;

    /**
     * UserFacade constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->statusValidatorContext = [
            new NoAccountFoundStrategy(),
            new PendingAccountStrategy(),
            new TerminatedAccountStrategy()
        ];
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @param string $userRole
     * @return User
     */
    public function createUser(array $data, $userRole)
    {
        if (!isset($data['organization_id'])) {
            $data['organization_id'] = 1;
        }

        $userFactory = $this->getUserFactory($userRole, $data['organization_id']);
        $user = $userFactory->createUser($data);

        return $user;
    }

    /**
     * Get the appropriate user factory based on user role and organization ID.
     *
     * @param string $userRole
     * @param int $organizationId
     * @return UserFactory
     */
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

    /**
     * Update a user's details.
     *
     * @param int $userId
     * @param array $data
     * @return User
     */
    public function updateUser($userId, array $data)
    {
        return $this->userRepository->update($userId, $data);
    }

    /**
     * Delete a user by ID.
     *
     * @param int $userId
     * @return User
     */
    public function deleteUser($userId)
    {
        return $this->userRepository->delete($userId);
    }

    /**
     * Find a user by ID.
     *
     * @param int $userId
     * @return User
     */
    public function findUser($userId)
    {
        return $this->userRepository->find($userId);
    }

    /**
     * Find users by organization ID.
     *
     * @param int $organizationId
     * @return \Illuminate\Support\Collection
     */
    public function findUsersByOrganization($organizationId)
    {
        return $this->userRepository->findByOrganization($organizationId);
    }

    /**
     * Find the first admin of an organization.
     *
     * @param int $organizationId
     * @return User|null
     */
    public function findFirstAdmin($organizationId)
    {
        return User::where('organization_id', $organizationId)
            ->where('user_role', 'admin')
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Validate the status of a user using predefined strategies.
     *
     * @param $user
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function validateUserStatus($user)
    {
        foreach ($this->statusValidatorContext as $strategy) {
            $response = $strategy->validate($user);
            if ($response !== null) {
                return $response;
            }
        }
        return null;
    }
}
