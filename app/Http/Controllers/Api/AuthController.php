<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Invalid credentials', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Invalid login details', 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success(['token' => $token, 'user' => $user], 'Logged in');
    }

    public function logout(Request $request)
    {
        $request->user()?->tokens()->delete();

        return $this->success([], 'Logged out');
    }

    public function me(Request $request)
    {
        return $this->success($request->user(), 'Authenticated user');
    }
}
