<?php

namespace App\Http\Controllers;

use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *  Test
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            $credentials = request(['email', 'password']);
            $expiration = Carbon::now()->addDays(7)->timestamp;
            $userData = new UserRepository(new User());
            $user = $userData->findbyparam('email', request('email'));
            $details = isset($user->details) ? $user->details : null;
            $payload = [
                'type' => $details['type'],
            ];

            JWTAuth::factory()->setTTL($expiration);
            if (!$token = auth()->claims($payload)->attempt($credentials, ['exp' => $expiration])) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $dataResponse = new UserResource($user);

            return $this->respondWithToken($token, $expiration, $dataResponse);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $expiration, $data)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration,
            'data' => $data,
        ]);
    }
}
