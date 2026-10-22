<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─────────────────────────────────────────────
    //  REGISTER
    // ─────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:100',
            'cnic'             => 'required|string|regex:/^\d{5}-\d{7}-\d{1}$/|unique:users,cnic',
            'mobile'           => 'required|string|max:15|unique:users,mobile',
            'email'            => 'required|email|max:100|unique:users,email',
            'password'         => 'required|string|min:8|confirmed',
            'username'         => 'nullable|string|max:50|unique:users,username',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $username = $request->username
            ?? str_replace('-', '', $request->cnic);

        $cleanMobile = str_replace('-', '', $request->mobile);

        $user = User::create([
            'name'     => $request->name,
            'cnic'     => $request->cnic,
            'mobile'   => $cleanMobile,
            'email'    => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'role'     => 'citizen',
        ]);

        $token = $user->createToken('karachi_portal')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token'   => $token,
            'user'    => $this->userResponse($user),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  LOGIN
    // ─────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Allow login by username, CNIC, or mobile
        $user = User::where('username', $request->username)
            ->orWhere('cnic', $request->username)
            ->orWhere('mobile', $request->username)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials / غلط معلومات',
            ], 401);
        }

        // Revoke old tokens (single session)
        $user->tokens()->delete();
        $token = $user->createToken('karachi_portal')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $this->userResponse($user),
        ]);
    }

    // ─────────────────────────────────────────────
    //  LOGOUT
    // ─────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    // ─────────────────────────────────────────────
    //  ME
    // ─────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->userResponse($request->user()),
        ]);
    }

    // ─────────────────────────────────────────────
    //  UPDATE PROFILE
    // ─────────────────────────────────────────────
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'   => 'sometimes|string|max:100',
            'mobile' => "sometimes|string|max:15|unique:users,mobile,{$user->id}",
            'email'  => "sometimes|email|unique:users,email,{$user->id}",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data'    => $this->userResponse($user->fresh()),
        ]);
    }

    // ─────────────────────────────────────────────
    //  CHANGE PASSWORD
    // ─────────────────────────────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();
        $token = $user->createToken('karachi_portal')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            'token'   => $token,
        ]);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: List users
    // ─────────────────────────────────────────────
    public function adminListUsers(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('cnic', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) =>
                $q->where('role', $request->role))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function adminShowUser(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json(['success' => true, 'data' => $user]);
    }

    // ─────────────────────────────────────────────
    //  Helper
    // ─────────────────────────────────────────────
    private function userResponse(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'cnic'     => $user->cnic,
            'mobile'   => $user->mobile,
            'email'    => $user->email,
            'role'     => $user->role,
        ];
    }
}
