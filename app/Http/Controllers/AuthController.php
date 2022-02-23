<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\UserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    const TOKEN_NAME = 'test-token';

    public function generateTokenResponse(User $user)
    {
        $token =  $user->createToken(self::TOKEN_NAME);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where(['email' => $request->email])->first();

        // pass verification
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Email or password is incorrect.'
            ], 400);
        }

        return $this->generateTokenResponse($user);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return $this->generateTokenResponse($user);
    }

    public function githubLogin(Request $request)
    {
        $accessToken = Socialite::driver('github')->getAccessTokenResponse($request->code);
        if (isset($accessToken['error'])) {
            return response()->json([
                'success' => false,
                'error' => $accessToken['error_description']
            ], 401);
        }

        $githubUser = Socialite::driver('github')->userFromToken($accessToken['access_token']);

        if (!$githubUser) {
            return response()->json([
                'success' => false,
                'error' => 'XXX'
            ]);
        }

        $userProvider = UserProvider::where([
            'provider_id' => $githubUser->id,
            'provider_type' => UserProvider::PROVIDER_GITHUB
        ])->first();

        if ($userProvider) {
            return $this->generateTokenResponse($userProvider->user);
        }

        try {
            $name = explode(' ', $githubUser->name);
            DB::beginTransaction();

            $user = User::create([
                'firstname' => $name[0],
                'lastname' => isset($name[1]) ? $name[1]: '' ,
                'email' => $githubUser->email,
                'password' => Hash::make('Password')
            ]);

            UserProvider::create(
                [
                    'user_id' => $user->id,
                    'provider_id' => $githubUser->id,
                    'provider_type' => 'github',
                    'access_token' => $accessToken['access_token']
                ]
            );

            DB::commit();

            return $this->generateTokenResponse($user);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
