<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class UserDataController extends Controller
{
    public function update(Request $request)
    {
        try {
            $request->validate([
                'nama_depan' => 'required|string|max:255',
                'nama_belakang' => 'required|string|max:255',
                'no_hp' => 'nullable|string|max:15',
                'alamat_tinggal' => 'nullable|string',
                'asal_kota' => 'nullable|string|max:255',
                'asal_provinsi' => 'nullable|string|max:255',
                'kodepos' => 'nullable|string|max:10',
            ]);

            $user = Auth::user();
            $user->update($request->all());

            Alert::success('Selamat', 'Profil Anda telah berhasil diperbarui.');
            return redirect()->route('user.home')->with('success', 'Profile berhasil diperbarui!');
        } catch (Exception $e) {
            Alert::error('error', 'Gagal memperbarui profile: ' . $e->getMessage());
            return redirect()->route('user.home')->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }
}
