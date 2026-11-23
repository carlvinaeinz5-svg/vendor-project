<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $defaultDestination = match (Auth::user()->role) {
                'admin' => route('admin.dashboard'),
                'vendor' => route('vendor.dashboard'),
                default => route('marketplace'),
            };

            return redirect()->intended($defaultDestination);
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('marketplace');
    }

    public function registerForm(Request $request)
    {
        $defaultRole = $request->routeIs('vendor.register') ? 'vendor' : 'customer';

        return view('auth.register', [
            'categories' => Category::orderBy('name')->get(),
            'defaultRole' => old('role', $defaultRole),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['customer', 'vendor'])],
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'required_if:role,vendor', 'string', 'max:30'],
            'store_name' => ['nullable', 'required_if:role,vendor', 'string', 'max:150'],
            'category_id' => ['nullable', 'required_if:role,vendor', 'exists:categories,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            if ($data['role'] === 'vendor') {
                Vendor::create([
                    'user_id' => $user->id,
                    'category_id' => $data['category_id'],
                    'store_name' => $data['store_name'],
                    'phone' => $data['phone'],
                    'status' => 'pending',
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'vendor') {
            return redirect()->route('vendor.dashboard')
                ->with('success', 'Vendor application submitted. Awaiting administrator approval.');
        }

        return redirect()->route('marketplace')
            ->with('success', 'Your customer account has been created.');
    }

    public function registerVendor(Request $request)
    {
        if (! $request->has('role')) {
            $request->merge(['role' => 'vendor']);
        }

        return $this->register($request);
    }
}
