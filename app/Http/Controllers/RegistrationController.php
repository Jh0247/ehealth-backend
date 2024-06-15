<?php

namespace App\Http\Controllers;

use App\Facades\HealthRecordFacade;
use App\Facades\UserFacade;
use App\Factories\AdminUserFactory;
use App\Factories\NormalUserFactory;
use App\Factories\StaffUserFactory;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\UserRegistrationValidationStrategy;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    protected $registrationValidatorContext;
    protected $userFacade;
    protected $healthRecordFacade;

    public function __construct(UserFacade $userFacade, HealthRecordFacade $healthRecordFacade)
    {
        $this->registrationValidatorContext = new ValidatorContext();
        $this->registrationValidatorContext->addStrategy(new UserRegistrationValidationStrategy());
        $this->userFacade = $userFacade;
        $this->healthRecordFacade = $healthRecordFacade;
    }

    // Regular user registration
    public function registerUser(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        // Facade create normal user
        $data = array_merge($request->all(), ['organization_id' => 1]);
        $user = $this->userFacade->createUser($data, 'user');

        // Facade create health record
        $this->healthRecordFacade->createHealthRecordForUser($user->id);

        return response()->json([
            'message' => 'Account has been successfully registered',
            'user' => $user
        ], 201);
    }

    // Admin registration
    public function registerAdmin(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        // Facade create ehealth admin
        $data = array_merge($request->all(), ['organization_id' => 1]);
        $admin = $this->userFacade->createUser($data, 'admin');

        return response()->json([
            'message' => 'Admin account has been successfully created',
            'user' => $admin
        ], 201);
    }

    // Staff registration
    public function registerStaff(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $staff = $this->userFacade->createUser($request->all(), 'staff');

        // Facade create health record
        $this->healthRecordFacade->createHealthRecordForUser($staff->id);

        return response()->json([
            'message' => 'Staff account has been successfully created',
            'user' => $staff
        ], 201);
    }
}
