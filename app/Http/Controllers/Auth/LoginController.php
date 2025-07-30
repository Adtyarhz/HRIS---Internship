<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function username()
    {
        return 'name';
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('name', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'name' => 'Username atau password salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
    public function editLogin($id)
{
    $employee = Employee::findOrFail($id);
    $user = $employee->user;
    $roles = ['superadmin', 'direksi', 'manager', 'section_head', 'staff_bisnis', 'staff_support'];
    return view('employees.data.edit-login', compact('employee', 'user', 'roles'));
}

public function updateLogin(Request $request, $id)
{
    $employee = Employee::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255', // login name
        'email' => 'required|email',
        'role' => 'required|string',
        'password' => 'nullable|min:6|confirmed',
    ]);

    $user = $employee->user ?? new User();

    $user->name = $request->name;
    $user->email = $request->email;
    $user->role = $request->role;

    if ($request->filled('password')) {
        $user->password = bcrypt($request->password);
    }

    $user->save();

    if (!$employee->user_id) {
        $employee->user_id = $user->id;
        $employee->save();
    }

    return redirect()->route('employees.show', $employee->id)->with('success', 'Login account updated.');
}
}
