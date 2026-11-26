<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CitizenProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->loadCount('reports');

        return view('citizen.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'kecamatan' => [
                'nullable',
                'string',
                'max:100',
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('profile_photo')) {
            $newPhoto = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $validated['profile_photo'] = $newPhoto;
        }

        $user->update($validated);

        return redirect()
            ->route('citizen.profile')
            ->with('success', 'Profil berhasil diperbarui!');
    }
}
