<?php

namespace App\Http\Controllers;

use App\Facades\HealthRecordFacade;
use App\Facades\UserFacade;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\UserRegistrationValidationStrategy;
use Illuminate\Http\Request;

/**
 * RegistrationController handles the registration of different types of users.
 */
class RegistrationController extends Controller
{
    /**
     * @var ValidatorContext
     */
    protected $registrationValidatorContext;

    /**
     * @var UserFacade
     */
    protected $userFacade;

    /**
     * @var HealthRecordFacade
     */
    protected $healthRecordFacade;

    /**
     * RegistrationController constructor.
     *
     * @param UserFacade $userFacade
     * @param HealthRecordFacade $healthRecordFacade
     */
    public function __construct(UserFacade $userFacade, HealthRecordFacade $healthRecordFacade)
    {
        $this->registrationValidatorContext = new ValidatorContext();
        $this->registrationValidatorContext->addStrategy(new UserRegistrationValidationStrategy());
        $this->userFacade = $userFacade;
        $this->healthRecordFacade = $healthRecordFacade;
    }

    /**
     * Register a regular user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUser(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $data = array_merge($request->all(), ['organization_id' => 1]);
        $user = $this->userFacade->createUser($data, 'user');

        $this->healthRecordFacade->createHealthRecordForUser($user->id);

        return response()->json([
            'message' => 'Account has been successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Register an admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerAdmin(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $data = array_merge($request->all(), ['organization_id' => 1]);
        $admin = $this->userFacade->createUser($data, 'admin');

        return response()->json([
            'message' => 'Admin account has been successfully created',
            'user' => $admin
        ], 201);
    }

    /**
     * Register a staff member.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerStaff(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $staff = $this->userFacade->createUser($request->all(), 'staff');

        $this->healthRecordFacade->createHealthRecordForUser($staff->id);

        return response()->json([
            'message' => 'Staff account has been successfully created',
            'user' => $staff
        ], 201);
    }
}
