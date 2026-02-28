<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class SignupController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5',
            'email' => 'required|string|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/[A-Za-z]/', $value) || !preg_match('/[0-9]/', $value)) {
                        $fail('The password must contain at least one letter and one number.');
                    }
                },
            ],
            'contact_number' => 'required|string|min:9|max:13',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_number' => $request->contact_number,
            'role' => 'user',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $user
        ], 201);
    }
}