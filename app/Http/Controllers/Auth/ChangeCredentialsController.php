<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangeCredentialsController extends Controller
{
    public function edit()
    {
        return view('auth.change-credentials');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username,' . auth()->id(),
            'password' => 'required|string|min:3|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->must_change_credentials = false;
        $user->save();

        $redirect = match (auth()->user()->role) {
    'admin' => route('admin'),
    'super-admin' => route('super-admin'),
    'finance' => route('finance'),
    'member' => route('member'),
    default => route('dashboard'),
};

return response()->json([
    'success' => 'Username dan password berhasil diperbarui.',
    'redirect' => $redirect,
]);
    }
}