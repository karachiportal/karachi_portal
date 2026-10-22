<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role','citizen')
            ->withCount('complaints')
            ->orderByDesc('created_at');

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('name','like',"%$s%")
                  ->orWhere('cnic','like',"%$s%")
                  ->orWhere('mobile','like',"%$s%")
                  ->orWhere('username','like',"%$s%")
            );
        }

        $users        = $query->paginate(25);
        $pendingCount = Complaint::where('status','pending')->count();
        return view('users.index', compact('users','pendingCount'));
    }

    public function show($id)
    {
        $user         = User::withCount('complaints')->findOrFail($id);
        $complaints   = Complaint::where('user_id', $id)->orderByDesc('created_at')->limit(10)->get();
        $pendingCount = Complaint::where('status','pending')->count();
        return view('users.show', compact('user','complaints','pendingCount'));
    }
}
