<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function restoLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $credentials['email'])->with('detailResto')->first();

            if (!$user) {
                return response()->json(['error' => 'Email Tidak Terdaftar'], Response::HTTP_UNAUTHORIZED);
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json(['error' => 'Password salah'], Response::HTTP_UNAUTHORIZED);
            }
            $token = $user->createToken('auth_token')->plainTextToken;

            $user->fcm = $request->fcm;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Login Berhasil',
                'data' => $user,
                'token' => $token
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function driverLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $credentials['email'])->with('detailDriver')->first();

            if (!$user) {
                return response()->json(['error' => 'Email Tidak Terdaftar'], Response::HTTP_UNAUTHORIZED);
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json(['error' => 'Password salah'], Response::HTTP_UNAUTHORIZED);
            }
            $token = $user->createToken('auth_token')->plainTextToken;

            $user->fcm = $request->fcm;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Login Berhasil',
                'data' => $user,
                'token' => $token
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
