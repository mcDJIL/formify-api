<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:5',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('token-name')->plainTextToken;

        $user['accessToken'] = $token;

        return response()->json([ 'user' => $user ], 200);
    }

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        if ($validation->fails()) return response()->json([ 'message' => 'Invalid field', 'errors' => $validation->errors() ], 422);

        $credentials = $request->only([ 'email', 'password' ]);

        if (Auth::attempt($credentials))
        {
            $user = Auth::user();
            $token = $user->createToken('token-name')->plainTextToken;

            $user['accessToken'] = $token;

            return response()->json([ 'message' => 'Login success', 'user' => $user ], 200);
        } 
        else
        {
            return response()->json([ 'message' => 'Email or password incorrect' ], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([ 'message' => 'Logout success' ], 200);
    }
}
