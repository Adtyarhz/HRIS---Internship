<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;

class AuthApiController extends Controller
{
    /**
     * Standardized API Response
     */
    private function apiResponse($success, $message = null, $data = null, $status = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], $status);
    }

    /**
     * Format user data 
     */
    private function formatUserData(User $user)
    {
        $employee = $user->employee;
        return [
            'email' => $user->email,
            'name'  => $user->name,
            'role'  => $user->role,
            'division'  => $employee?->division?->name,
            'position'  => $employee?->position?->title,
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->apiResponse(false, 'Invalid credentials', null, 401);
        }

        return $this->apiResponse(
            true,
            'Login successful',
            $this->formatUserData($user)
        );
    }

    public function getUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->apiResponse(false, 'User not found', null, 404);
        }

        return $this->apiResponse(
            true,
            'User retrieved',
            $this->formatUserData($user)
        );
    }

    public function logout(Request $request)
    {
        return $this->apiResponse(true, 'Logged out successfully');
    }
}
