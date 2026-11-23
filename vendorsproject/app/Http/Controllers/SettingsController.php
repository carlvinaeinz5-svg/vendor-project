<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('settings', [
            'user' => $request->user(),
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $oldImagePath = $user->profile_image_path;
        unset($data['profile_image']);

        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile-photos', 'public');
            if (! $imagePath) {
                throw new RuntimeException('The profile image could not be saved.');
            }
            $data['profile_image_path'] = $imagePath;
        }

        $user->update($data);

        if (isset($data['profile_image_path']) && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return back()->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Your password has been updated.');
    }

    public function updatePreferences(Request $request)
    {
        $data = $request->validate([
            'theme' => ['required', Rule::in(['market', 'sunrise', 'ocean'])],
            'preferred_payment_method' => ['sometimes', Rule::in(array_keys(Payment::METHODS))],
            'email_notifications' => ['sometimes', 'boolean'],
        ]);

        $request->user()->update([
            'theme' => $data['theme'],
            'preferred_payment_method' => $data['preferred_payment_method'] ?? $request->user()->preferred_payment_method,
            'email_notifications' => $request->boolean('email_notifications'),
        ]);

        return back()->with('success', 'Your preferences have been saved.');
    }
}
