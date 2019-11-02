<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{
    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    public function index(Request $request)
    {
        return response()->json([
            'data' => $request->user()
        ])
        ->setStatusCode(Response::HTTP_OK);
    }

    public function logout()
    {
        $this->auth->invalidate();

        return response()->json([
            'success' => true
        ])
        ->setStatusCode(Response::HTTP_OK);
    }
}
