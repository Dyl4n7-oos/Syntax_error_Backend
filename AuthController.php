<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:customer,email',
            'password' => 'required|min:8',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'is_verified' => false,
        ]);

        $this->sendOtp($customer->email);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Check your email for OTP.',
            'email' => $customer->email
        ]);
    }

    // Login - Step 1: Verify credentials, send OTP
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $this->sendOtp($customer->email);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email',
            'email' => $customer->email
        ]);
    }

    // Verify OTP and complete login
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $otp = Otp::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 401);
        }

        $otp->is_used = true;
        $otp->save();

        $customer = Customer::where('email', $request->email)->first();
        
        if (!$customer->is_verified) {
            $customer->is_verified = true;
            $customer->save();
        }

        // Delete old tokens and create new
        $customer->tokens()->delete();
        $token = $customer->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $customer->user_ID,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'token' => $token
        ]);
    }

    // Resend OTP
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $this->sendOtp($request->email);
        
        return response()->json([
            'success' => true,
            'message' => 'New OTP sent to your email'
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // Get authenticated user
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    // Generate and send OTP (logs to file for development)
    private function sendOtp($email)
    {
        // Delete old OTPs
        Otp::where('email', $email)->delete();
        
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        Otp::create([
            'email' => $email,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
        ]);
        
        // Log OTP for development (check storage/logs/laravel.log)
        Log::info("=========================================");
        Log::info("OTP for {$email}: {$otpCode}");
        Log::info("=========================================");
        
        return $otpCode;
    }
}