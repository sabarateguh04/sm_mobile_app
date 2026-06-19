<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AppSettingController extends Controller
{
    public function logo()
    {
        return view('admin.settings.logo');
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ], [
            'logo.required' => 'File logo harus diunggah.',
            'logo.image' => 'File harus berupa gambar.',
            'logo.mimes' => 'Format yang diizinkan hanya: jpeg, png, jpg, svg.',
            'logo.max' => 'Ukuran maksimal file adalah 2MB.',
        ]);

        $file = $request->file('logo');
        
        // Simpan langsung sebagai logo-korlantas.png agar mereplace logo lama.
        // Jika format aslinya png/jpeg/svg akan tersimpan dengan nama ini namun MIME tetap terbaca oleh browser.
        $filename = 'logo-korlantas.png';
        $destinationPath = public_path('images');

        $file->move($destinationPath, $filename);

        return back()->with('success', 'Logo aplikasi berhasil diperbarui.');
    }
}
