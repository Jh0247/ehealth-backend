<?php

namespace App\Http\Controllers;

use App\Services\Validation\ValidatorContext;
use App\Services\Validation\EmailPasswordValidationStrategy;
use App\Services\Validation\EmailExistsValidationStrategy;
use App\Services\Validation\UserRegistrationValidationStrategy;
use App\Services\UserStatusValidation\UserStatusStrategyInterface;
use App\Services\UserStatusValidation\NoAccountFoundStrategy;
use App\Services\UserStatusValidation\PendingAccountStrategy;
use App\Services\UserStatusValidation\TerminatedAccountStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\HealthRecord;

class AuthController extends Controller
{
    protected $loginValidatorContext;
    protected $registrationValidatorContext;
    protected $statusValidatorContext;

    public function __construct()
    {
        $this->loginValidatorContext = new ValidatorContext();
        $this->loginValidatorContext->addStrategy(new EmailPasswordValidationStrategy());
        $this->loginValidatorContext->addStrategy(new EmailExistsValidationStrategy());

        $this->registrationValidatorContext = new ValidatorContext();
        $this->registrationValidatorContext->addStrategy(new UserRegistrationValidationStrategy());

        $this->statusValidatorContext = [
            new NoAccountFoundStrategy(),
            new PendingAccountStrategy(),
            new TerminatedAccountStrategy()
        ];
    }

    // login function
    public function login(Request $request)
    {
        // login validator context
        $validationResult = $this->loginValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $user = User::where('email', $request->email)->first();

        // Use the strategies to validate user status
        foreach ($this->statusValidatorContext as $strategy) {
            $response = $strategy->validate($user);
            if ($response !== null) {
                return $response;
            }
        }

        if (!auth()->attempt($request->only('email', 'password'))) {
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
