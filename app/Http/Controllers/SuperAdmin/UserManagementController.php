<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

class UserManagementController extends Controller
{
    public function index()
    {
        $data = User::all();
        return view('superadmin.users.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_depan' => 'required|string|max:255',
            'nama_belakang' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_type' => 'required|in:0,1',
            'gender' => 'required|in:Laki,P',
            'no_hp' => 'nullable|string|max:20',
        ]);

        try {
            User::create([
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_type' => $request->role_type,
                'gender' => $request->gender,
                'no_hp' => $request->no_hp,
            ]);

            Alert::success('Berhasil', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('dashboard.super.users');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_depan' => 'required|string|max:255',
            'nama_belakang' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_type' => 'required|in:0,1',
            'gender' => 'required|in:Laki,P',
            'no_hp' => 'nullable|string|max:20',
        ]);

        try {
            $user = User::findOrFail($id);
            $user->update([
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'email' => $request->email,
                'role_type' => $request->role_type,
                'gender' => $request->gender,
                'no_hp' => $request->no_hp,
            ]);

            Alert::success('Berhasil', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat update: ' . $e->getMessage());
        }

        return redirect()->route('dashboard.super.users');
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            Alert::success('Berhasil', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat menghapus user. ' . $e->getMessage());
        }

        return redirect()->route('dashboard.super.users');
    }
}
