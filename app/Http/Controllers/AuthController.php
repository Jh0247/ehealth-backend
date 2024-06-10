<?php

namespace App\Http\Controllers;

use App\Services\Validation\ValidatorContext;
use App\Services\Validation\EmailPasswordValidationStrategy;
use App\Services\Validation\EmailExistsValidationStrategy;
use App\Services\Validation\UserRegistrationValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\HealthRecord;

class AuthController extends Controller
{
    protected $loginValidatorContext;
    protected $registrationValidatorContext;

    public function __construct()
    {
        $this->loginValidatorContext = new ValidatorContext();
        $this->loginValidatorContext->addStrategy(new EmailPasswordValidationStrategy());
        $this->loginValidatorContext->addStrategy(new EmailExistsValidationStrategy());

        $this->registrationValidatorContext = new ValidatorContext();
        $this->registrationValidatorContext->addStrategy(new UserRegistrationValidationStrategy());
    }

    // login function
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::where('email', $request->email)->first();
        //all if else strategy
        // Check if the user exists and validate the status
        if (!$user) {
            return response()->json(['error' => 'No account found.'], 401);
        } elseif ($user->status === 'pending') {
            return response()->json(['error' => 'This account is currently under review, please wait for 1 to 3 working days.'], 403);
        } elseif ($user->status === 'terminated') {
            return response()->json(['error' => 'This account has been terminated, please contact admin for further assistance.'], 403);
        }

        if (!auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Invalid Password'], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Welcome back ' . $user->name,
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // normal user registration function
    public function userRegister(Request $request)
    {
        $validationResult = $this->registrationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json($validationResult['errors']->toJson(), 400);
        }

        // factory
        $user = User::create([
            'organization_id' => 1,
            'name' => $request->name,
            'email' => $request->email,
            'icno' => $request->icno,
            'contact' => $request->contact,
            'password' => Hash::make($request->password),
            'user_role' => 'user',
            'status' => 'active'
        ]);
        // buider
        HealthRecord::create([
            'user_id' => $user->id,
            'health_condition' => null,
            'blood_type' => null,
            'allergic' => null,
            'diseases' => null,
        ]);

        return response()->json([
            'message' => 'Account had been successfully registered',
            'user' => $user
        ], 201);
    }

}
