<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeviceAuthRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Device;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    /**
     * Create a new Sanctum token.
     * 
     * @return \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie
     */
    private function createToken(User $user): \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie
    {
        $token = $user->createToken(
            'sinau_rek_token',
            ['*'],
            now()->addMinutes((int)config('session.lifetime'))
        )->plainTextToken;

        $user->token = $token;
        
        $cookie = cookie(
            'sinau_rek_token',
            $token,
            config('session.lifetime')
        );

        return $cookie;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = $request->register();

            if (! $user) {
                return response()->json([
                    'message' => 'Failed to register. Please try again later.',
                ], 401);
            }

            $cookie = $this->createToken($user);

            return response()
                    ->json([
                        'user' => new UserResource($user),
                        'message' => 'Registeration Successful.',
                    ])
                    ->withCookie($cookie);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error registering. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function login(LoginRequest $request)
    {  
        try {
            $user = $request->authenticate();

            if (! $user) {
                return response()->json([
                    'message' => 'Failed to login. Please try again.',
                ], 401);
            }

            $cookie = $this->createToken($user);

            return response()
                    ->json([
                        'user' => new UserResource($user),
                        'message' => 'Login Successful.',
                    ])
                    ->withCookie($cookie);
        }
        catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error logging in. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function authDevice(DeviceAuthRequest $request) {
        try {
            $request->authenticate();

            return response()->json([
                'message' => 'Device authenticated.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error getting device.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    static public function verifyDevice(string $mac_address, string $hmac, string $timestamp): ?Device {
        try {
            
            $device = Device::where('mac_address', $mac_address)->first();

            if (! isset($device)) {
                return null;
            }

            $message = $timestamp;

            $expectedHMAC = hash_hmac('sha256', $message, $device->secret_key);
            if (!hash_equals($expectedHMAC, $hmac)) {
                return null;
            }

            return $device;
        } catch (Exception $e) {
            return null;
        }
    }
}
