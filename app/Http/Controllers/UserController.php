<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();

        return response()->json([
            'users' => $users
        ]);
    }

    public function loadMe()
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user
        ]);
    }
}
