<?php

namespace App\Http\Controllers;

use App\Services\Validation\ValidatorContext;
use App\Services\Validation\EmailPasswordValidationStrategy;
use App\Services\Validation\EmailExistsValidationStrategy;
use App\Facades\UserFacade;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * Class AuthController
 * @package App\Http\Controllers
 *
 * This controller handles authentication operations.
 */
class AuthController extends Controller
{
    /**
     * @var ValidatorContext The context for login validation strategies.
     */
    protected $loginValidatorContext;

    /**
     * @var UserFacade
     */
    protected $userFacade;

    /**
     * AuthController constructor.
     *
     * Initializes the validation strategies for login and user status.
     */
    public function __construct(UserFacade $userFacade)
    {
        $this->loginValidatorContext = new ValidatorContext();
        $this->loginValidatorContext->addStrategy(new EmailPasswordValidationStrategy());
        $this->loginValidatorContext->addStrategy(new EmailExistsValidationStrategy());

        $this->userFacade = $userFacade;
    }

    /**
     * Handles user login.
     *
     * @param Request $request The HTTP request object containing login credentials.
     * @return \Illuminate\Http\JsonResponse The HTTP response object containing the result of the login attempt.
     *
     * This function validates the user's credentials and status using predefined strategies.
     * If validation passes, it attempts to log in the user and returns an authentication token.
     */
    public function login(Request $request)
    {
        // Perform login validation
        $validationResult = $this->loginValidatorContext->validate($request);

        // Return validation errors if any
        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        // Retrieve the user by email
        $user = User::where('email', $request->email)->first();

        // Validate user status using UserFacade
        $response = $this->userFacade->validateUserStatus($user);
        if ($response !== null) {
            return $response;
        }

        // Attempt to authenticate the user
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid Password'], 401);
        }

        $user->load('organization');

        // Create authentication token for the user
        $token = $user->createToken('authToken')->plainTextToken;

        // Return success response with the authentication token
        return response()->json([
            'message' => 'Welcome back ' . $user->name,
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
