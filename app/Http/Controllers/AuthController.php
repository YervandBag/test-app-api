<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    const TOKEN_NAME = 'test-token';

    public function login(Request $request)
    {
        $user = User::where(['email' => $request->email])->first();

        if (!$user) {
            return response()->json([
                'error' => 'Email or password is incorrect.'
            ], 400);
        }

        // pass verification

        $token = $user->createToken(self::TOKEN_NAME);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'user' => $user
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token =  $user->createToken(self::TOKEN_NAME);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'user' => $user
        ]);
    }
}
