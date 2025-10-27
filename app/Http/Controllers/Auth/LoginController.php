<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    //public function username()
    //{
      //  return 'name';
    //}

    public function login(Request $request)
{
    try {
        $request->validate([
            'login' => 'required|string', // bisa email atau name
            'password' => 'required|string',
        ]);

        // Cek apakah input berupa email atau username
        $loginInput = $request->input('login');
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->password,
        ];

        Log::info('Login attempt', ['login' => $loginInput, 'type' => $fieldType]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            Log::info('Login berhasil', ['user_id' => $user->id, 'role' => $user->role]);

            switch ($user->role) {
                case 'superadmin':
                case 'hc':
                case 'staff_bisnis':
                case 'staff_support':
                case 'manager':
                case 'section_head':
                case 'direksi':
                    return redirect()->route('dashboard');
                default:
                    Auth::logout();
                    Log::warning('Role tidak dikenali', ['role' => $user->role]);
                    return back()->withErrors(['login' => 'Role tidak dikenali.']);
            }
        }

        Log::warning('Login gagal', ['login' => $loginInput]);
        return back()->withErrors([
            'login' => 'Username/email atau password salah.',
        ])->withInput();

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error during login', ['errors' => $e->errors()]);
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Error during login', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->withErrors([
            'login' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
        ])->withInput();
    }
}

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Log::info('Logout berhasil', ['user_id' => $user->id]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }

    public function editLogin($id)
    {
        $employee = Employee::findOrFail($id);
        $user = $employee->user;
        $roles = ['staff_bisnis', 'staff_support', 'manager', 'section_head', 'direksi', 'hc'];
        return view('employees.data.edit_login', compact('employee', 'user', 'roles'));
    }

    public function updateLogin(Request $request, $id)
{
    $employee = Employee::findOrFail($id);
    $user = $employee->user;

    if (!$user) {
        $user = new User();
        $user->email = $request->email ?? $employee->email ?? strtolower(Str::slug($request->name)) . '@example.com';
        $user->password = Hash::make($request->password);
    }
    // 🔑 Batasi akses ubah email & role
    if (in_array(Auth::user()->role, ['superadmin', 'hc'])) {
        // superadmin & hc boleh edit semua
        $user->email = $request->email;
        $user->role  = $request->role;
    } else {
        // role lain: kunci email & role ke value lama
        $request->merge([
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }
    $request->validate([
        'name' => 'required|string|max:255|unique:users,name,' . ($user->id ?? 'null'),
        'email' => 'required|email|unique:users,email,' . ($user->id ?? 'null'),
        'role' => 'required|string|in:superadmin,staff_bisnis,staff_support,manager,section_head,direksi,hc',
        'password' => $user->exists ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
    ]);

    $user->name = $request->name;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    // Hubungkan ke employee
    $employee->user_id = $user->id;
    $employee->save();

    return redirect()->route('employees.data.edit_login', $employee->id)
        ->with('success', 'Data login berhasil diperbarui.');
}

    public function resetPassword(Request $request, $id)
{
    $employee = Employee::findOrFail($id);

    if (!$employee->user) {
        return redirect()->back()->with('error', 'This employee does not have a linked user account.');
    }

    $user = $employee->user;

    if ($employee->nip) {
        $user->password = Hash::make($employee->nip);
        $user->save();

        Log::info('Password reset', [
            'user_id' => $user->id,
            'employee_id' => $employee->id,
            'reset_to' => 'employee_nip'
        ]);

        return redirect()->back()->with('success', 'Password has been reset to the employee\'s NIP.');
    } else {
        return redirect()->back()->with('error', 'Cannot reset password because the employee has no NIP.');
    }
}
}
