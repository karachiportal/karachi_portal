<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->orWhere('cnic', $request->username)
            ->first();

        if (! $user || !in_array($user->role, ['admin', 'staff']) || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['username' => 'Invalid credentials or not an admin.']);
        }

        Auth::login($user, true);
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function showChangePassword()
    {
        return view('admin.change-password');
    }

    public function updatePassword(Request $request)
    {
        $this->denyIfViewer();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.change-password')
            ->with('success', 'Password changed successfully');
    }
}
