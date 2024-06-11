<?php

namespace App\Http\Controllers;

use App\Factories\AdminUserFactory;
use App\Factories\NormalUserFactory;
use App\Factories\StaffUserFactory;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\UserRegistrationValidationStrategy;
use Illuminate\Http\Request;
use App\Models\HealthRecord;

class RegistrationController extends Controller
{
    protected $registrationValidatorContext;

    public function __construct()
    {
        $this->registrationValidatorContext = new ValidatorContext();
        $this->registrationValidatorContext->addStrategy(new UserRegistrationValidationStrategy());
    }

    // Regular user registration
    public function registerUser(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json($validationResult['errors']->toJson(), 400);
        }

        $userFactory = new NormalUserFactory();
        $user = $userFactory->createUser($request->all());

        HealthRecord::create([
            'user_id' => $user->id,
            'health_condition' => null,
            'blood_type' => null,
            'allergic' => null,
            'diseases' => null,
        ]);

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
            return response()->json($validationResult['errors']->toJson(), 400);
        }

        $adminFactory = new AdminUserFactory();
        $admin = $adminFactory->createUser($request->all());

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
            return response()->json($validationResult['errors']->toJson(), 400);
        }

        $staffFactory = new StaffUserFactory();
        $staff = $staffFactory->createUser($request->all());

        HealthRecord::create([
            'user_id' => $staff->id,
            'health_condition' => null,
            'blood_type' => null,
            'allergic' => null,
            'diseases' => null,
        ]);

        return response()->json([
            'message' => 'Staff account has been successfully created',
            'user' => $staff
        ], 201);
    }
}
