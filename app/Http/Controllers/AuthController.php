<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'mobile' => ['required', 'exists:users,mobile'],
            'password' => ['required']
        ]);
        $user = User::where('mobile', $data['mobile'])->first();
        abort_unless(Hash::check($data['password'], $user->password), ResponseStatus::UNAUTHENTICATED->value);
        $user->tokens()->delete();
        $token = $user->createToken('login');
        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
