<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    private const TOKEN_NAME = "AUTH TOKEN";

    /**
     * Summary of register
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function registerUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'phoneNumber' => 'required|regex:/(01)[0-9]{9}/|unique:users,phoneNumber',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'phoneNumber' => $request->phoneNumber,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User created Successfully',
                'name' => $user->name,
                'phoneNumber' => $user->phoneNumber,
                'password' => $user->password,
                'token' => $user->createToken(self::TOKEN_NAME)->plainTextToken
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function loginUser(Request $request)
    {
        try {

            $validateUser = validator::make(
                $request->all(),
                [
                    'phoneNumber' => 'required|regex:/(01)[0-9]{9}/',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only('phoneNumber', 'password'))) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Phone Number or Password doesn\'t match'
                    ],
                    401
                );
            }

            $user = User::where('phoneNumber', $request->phoneNumber)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'name' => $user->name,
                'phoneNumber' => $user->phoneNumber,
                'password' => $user->password,
                'token' => $user->createToken(self::TOKEN_NAME)->plainTextToken
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);

        }
    }

}
