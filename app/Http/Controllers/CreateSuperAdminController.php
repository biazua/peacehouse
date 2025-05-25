<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateSuperAdminController extends Controller
{
    // UNPROTECTED endpoint: call /create-super-admin to create a super admin user
    public function create()
    {
        // Check if admin already exists
        $existing = User::where('email', 'ajoku.emmanuel@gmail.com')->first();
        if ($existing) {
            return response()->json(['message' => 'Admin already exists!'], 400);
        }

        $user = User::create([
            'first_name' => 'Emmanuel',
            'last_name' => 'Ajoku',
            'email' => 'ajoku.emmanuel@gmail.com',
            'password' => Hash::make('GOTOHESSgildas1@1.'),
            'is_admin' => 1,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Super admin created!',
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
