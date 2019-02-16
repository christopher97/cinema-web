<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function register(Request $request) {
        $user = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'ic_number' => $request->ic_number,
            'contact_number' => $request->contact_number
        ];

        try {
            $user = $this->user->create($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User already exists'], 409);
        }

        $token = auth()->login($user);
        return $this->respondWithToken($token);
    }

    public function login(Request $request) {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function validateToken(Request $request) {
        return $this->refreshToken();
    }

    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refreshToken() {
        $token = auth()->refresh();

        return $this->respondWithToken($token);
    }

    public function respondWithToken($token) {
        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
