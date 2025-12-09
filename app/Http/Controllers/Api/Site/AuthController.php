<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Site\LoginRequest;
use App\Http\Requests\Api\Site\RegisterRequest;
use App\Http\Requests\Api\Site\UpdateProfileRequest;
use App\Models\User;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if(!$token = auth('customer')->attempt($request->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'customer' => auth('customer')->user(),
        ]);
    }

    function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $customer = User::create($data);

        $token = auth('customer')->login($customer);

        return $this->respondWithToken($token);
    }

    function logout()
    {
        auth('customer')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    function profile() {
        return response()->json(auth('customer')->user());
    }

    function updateProfile(UpdateProfileRequest $request) {
        $customer = auth('customer')->user();
        $data = $request->validated();
        $customer->update($data);
        return $this->profile();
    }
}
