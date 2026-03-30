<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'dietary_tags' => ['sometimes', 'array'],
            'dietary_tags.*' => [
                'string',
                'distinct',
                Rule::in(['vegan', 'no_sugar', 'no_cholesterol', 'gluten_free', 'no_lactose']),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        $user->profile()->create([
            'dietary_tags' => array_values($validated['dietary_tags'] ?? []),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {

        $credentials = $request->validate(
            [

                'email' => 'required|email',
                'password' => 'required|string'
            ]
        );

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
          $token = $user->createToken('api-token')->plainTextToken;

          return response()->json([
        'user' => $user,
        'token' => $token
    ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $token = $user?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
  
}
