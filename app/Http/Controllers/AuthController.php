<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = trim(strtolower($request->input('email')));
        $password = $request->input('password');

        if (empty($email) || empty($password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email dan password tidak boleh kosong.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'success',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah!',
            ]);
        }
    }

    public function register(Request $request)
    {
        $name = trim($request->input('name'));
        $phone = trim($request->input('phone'));
        $email = trim(strtolower($request->input('email')));
        $password = $request->input('password');

        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semua field wajib diisi.',
            ]);
        }

        $existingUser = User::where('email', $email)->count();
        if ($existingUser > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email sudah terdaftar!',
            ]);
        }

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'user',
        ]);

        return response()->json([
            'status' => 'success',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => 'user',
            ],
        ]);
    }
}
