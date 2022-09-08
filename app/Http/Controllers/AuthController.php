<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        Log::info($request->headers->get('x-requested-with'));
        $data = $request->validate([
            'mobile' => ['required', 'exists:users,mobile'],
            'password' => ['required']
        ]);
        $user = User::where('mobile', $data['mobile'])->first();
        abort_unless(Hash::check($data['password'], $user->password), ResponseStatus::UNAUTHENTICATED->value);
        abort_unless(($user->latestSubscription && $user->latestSubscription->active) || $user->isAdmin(), ResponseStatus::UNAUTHORIZED->value, 'No active subscription');
        $user->tokens()->delete();
        $token = $user->createToken('login');
        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => ['required'],
            'new_password' => ['required', 'confirmed'],
        ]);
        $user = $request->user();
        abort_unless(Hash::check($request->password, $user->password), ResponseStatus::UNAUTHORIZED->value);

        $user->password = bcrypt($request->new_password);
        $user->save();
        return response()->json("ok");
    }

    public function token(Request $request)
    {
        return response()->json($request->user());
    }
}
