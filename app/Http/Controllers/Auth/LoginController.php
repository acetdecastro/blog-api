<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '';

    protected $auth;

    private $RESPONSE_INVALID_CREDENTIALS = [
        'errors' => [
            'email' => [
                'Invalid email address or password.'
            ]
        ]
    ];

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return response()->json([
                'errors' => [
                    "You've been locked out."
                ]
            ])  
            ->setStatusCode(Response::HTTP_LOCKED);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        try {
            // If login attempt returns false
            if (! $token = $this->auth->attempt($request->only('email', 'password'))) {
                return response()
                ->json($this->RESPONSE_INVALID_CREDENTIALS)
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (JWTException $e) {
            return response()
            ->json($this->RESPONSE_INVALID_CREDENTIALS)
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'data' => $request->user(),
            'token' => $token
        ])
        ->setStatusCode(Response::HTTP_OK);
    }
}
