<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index()
    {
        $users = User::latest()->get();
        // Additional stat passing for the view
        $totalUsers = $users->count();
        $admins = $users->where('role', 'admin')->count();
        $officers = $users->where('role', 'security')->count();
        $regular = $users->where('role', 'vehicle_user')->count();

        return view('admin.users.index', compact('users', 'totalUsers', 'admins', 'officers', 'regular'));
    }

    /**
     * Store a newly created secure account in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,security,vehicle_user'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now(), // Auto-verify internal accounts
        ]);

        return redirect()->back()->with('success', 'User account successfully generated!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own active administrator account.');
        }

        $user->delete();
        
        return redirect()->back()->with('success', 'User account permanently removed.');
    }
}
