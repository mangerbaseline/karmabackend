<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\SalonMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            $creds = $request->validated();

            if (!Auth::guard('web')->attempt($creds)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::guard('web')->user();
            $token = $user->createToken('spa')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $salons = SalonMember::where('user_id', $user->id)
            ->with('salon:id,name,slug,status,currency,timezone')
            ->get();

        return response()->json([
            'user' => $user,
            'salons' => $salons,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }
}
