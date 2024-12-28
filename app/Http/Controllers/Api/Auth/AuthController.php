<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'phone' => 'required|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);
        $user->cart()->create();
        $user->favorite()->create();


        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'profile_picture' => $user->profile_picture ? url("storage/{$user->profile_picture}") : null,
            'location' => $user->location,
            'locale' => $user->locale,
            'token'=>$token

        ];


        return response()->json([
            'user' => $userData,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
