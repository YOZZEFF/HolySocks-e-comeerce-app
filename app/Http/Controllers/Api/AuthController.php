<?php

namespace App\Http\Controllers\Api;

use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'avatar' =>   'nullable|image|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => bcrypt($request->password),
            'is_active' => true,
            'avatar'    => $avatarPath,
        ]);

        $user->assignRole('customer');

        $token = $user->createToken('auth_token')->plainTextToken;

        $userArray = $user->toArray();
        $userArray['roles'] = $user->getRoleNames();



        return response()->json([
            'message' => 'Registered successfully',
            'user'    => $userArray,
            'token'   => $token,

        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been suspended',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $userArray = $user->toArray();
        $userArray['roles'] = $user->getRoleNames();

        event(new UserLoggedIn($user));

        return response()->json([
            'message' => 'Logged in successfully',
            'user'    => $userArray,
            'token'   => $token,
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $userArray = $user->toArray();
        $userArray['roles'] = $user->getRoleNames();

        return response()->json([
            'user' => $userArray,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'   => 'sometimes|string|max:255',
            'phone'  => 'sometimes|string|max:20',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only(['name', 'phone']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);
        $user->refresh();
        $userArray = $user->toArray();
        $userArray['roles'] = $user->getRoleNames();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $userArray,
        ]);
    }


    public function logout(Request $request)
    {
        event(new UserLoggedOut($request->user()));

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
        ]);
    }
}
