<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    public function register(RegisterRequest $request)
    {

    }

    public function login(LoginRequest $request)
    {  
        try
        {
            $user = $request->authenticate();

            if (! $user) {
                return response()->json([
                    'message' => 'Failed to login. Please try again.',
                ], 401);
            }

            $token = $user->createToken(
                'sinau_rek_token',
                ['*'],
                now()->addMinutes(config('session.lifetime')),
            )->plainTextToken;
            
            $cookie = cookie(
                'sinau_rek_token',
                $token,
                config('session.lifetime'),
            );

            return response()
                    ->json([
                        'user' => new UserResource($user),
                        'message' => 'Login Successful.',
                    ])
                    ->withCookie($cookie);
        }
        catch (Exception $e)
        {
            return response()->json(
                [
                    'message' => 'Error logging in. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
