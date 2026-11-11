<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Memproses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user()->role);
    }

    // Menampilkan halaman registrasi
    public function showRegister()
    {
        return view('auth.register');
    }

    // Registrasi akun citizen
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Registrasi publik hanya boleh menghasilkan role citizen
        $user->forceFill(['role' => 'citizen'])->save();

        Auth::login($user);

        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Redirect berdasarkan role pengguna
    private function redirectByRole(string $role)
    {
        return match ($role) {
            'citizen' => redirect('/citizen/dashboard'),
            'officer' => redirect('/officer/dashboard'),
            'governance' => redirect('/governance/dashboard'),
            'superadmin' => redirect('/superadmin/dashboard'),
            default => abort(403, 'Role tidak dikenali.'),
        };
    }
}
