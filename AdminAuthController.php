<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    // Admin Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Delete old tokens
        $admin->tokens()->delete();
        $token = $admin->createToken('admin_token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin login successful',
            'admin' => [
                'id' => $admin->admin_ID,
                'name' => $admin->name,
                'email' => $admin->email,
                'role_level' => $admin->role_level
            ],
            'token' => $token
        ]);
    }

    // Admin Logout
    public function logout(Request $request)
    {
        $request->user('admin')->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // Get Admin User
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'admin' => $request->user('admin')
        ]);
    }
}