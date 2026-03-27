<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    public function update(Request $request)
    {
        $normalizedEmail = strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $normalizedEmail]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'affiliation' => ['nullable', 'string', 'max:100'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'contact_number' => $request->input('contact_number'),
            'affiliation' => $request->input('affiliation'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->input('password');
        }

        try {
            $request->user()->update($payload);
        } catch (QueryException $e) {
            // Handles race conditions where two updates target the same email.
            if (($e->errorInfo[1] ?? null) === 1062 || $e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'email' => 'This email is already used by another account.',
                ]);
            }
            throw $e;
        }

        return back()->with('status', 'Profile information updated.');
    }

    /**
     * Upload and auto-compress profile photo to ≤300KB using PHP GD.
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        $user = auth()->user();

        // Delete old photo if it exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $file = $request->file('profile_photo');
        $mime = $file->getMimeType();

        // Load image with GD
        if ($mime === 'image/png') {
            $src = imagecreatefrompng($file->getRealPath());
            $bg  = imagecreatetruecolor(imagesx($src), imagesy($src));
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagecopy($bg, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
            $src = $bg;
        } else {
            $src = imagecreatefromjpeg($file->getRealPath());
        }

        // Downscale if very large (max 1200px wide)
        $origW = imagesx($src);
        $origH = imagesy($src);
        $maxDim = 1200;
        if ($origW > $maxDim || $origH > $maxDim) {
            $scale   = min($maxDim / $origW, $maxDim / $origH);
            $newW    = (int)($origW * $scale);
            $newH    = (int)($origH * $scale);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        // Compress to JPEG, reducing quality until file size ≤ 300KB
        $targetBytes = 300 * 1024;
        $quality     = 85;
        $tempPath    = tempnam(sys_get_temp_dir(), 'profile_') . '.jpg';

        do {
            imagejpeg($src, $tempPath, $quality);
            $size    = filesize($tempPath);
            $quality -= 5;
        } while ($size > $targetBytes && $quality >= 20);

        imagedestroy($src);

        $filename   = 'profile_photos/' . $user->id . '_' . time() . '.jpg';
        $compressed = file_get_contents($tempPath);
        unlink($tempPath);

        Storage::disk('public')->put($filename, $compressed);
        $user->update(['profile_photo_path' => $filename]);

        return back()->with('status', 'Profile photo updated successfully!');
    }

    /**
     * Remove the user's profile photo.
     */
    public function removePhoto(Request $request)
    {
        $user = auth()->user();
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }
        return back()->with('status', 'Profile photo removed.');
    }
}
