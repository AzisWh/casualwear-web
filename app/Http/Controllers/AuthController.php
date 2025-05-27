<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class AuthController extends Controller
{
    public function indexLogin()
    {
        return view('auth.login');
    }

    public function indexRegister()
    {
        return view('auth.register');
    }

    public function fungsiLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            Alert::error('Gagal', 'Login gagal, silakan cek kembali data yang Anda masukkan.');
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            Alert::success('Hore!', 'Login berhasil');
            if(Auth::user()->role_type==0){
                // return redirect()->route('user.home');
            }
            else if(Auth::user()->role_type==1){
                return redirect()->route('admin.home');
            }
            return redirect()->route('login');
        }

        Alert::error('Gagal', 'Email atau password salah.');
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    public function fungsiRegister(Request $request)
    {
        $messages = [
            'email.unique' => 'Email ini sudah pernah didaftarkan, silakan gunakan email lain.',
            'password.min' => 'Password harus terdiri dari minimal 8 karakter.',
            'password.confirmed' => 'Password dan konfirmasi password tidak sama.',
            'nama_depan.required' => 'Nama depan wajib diisi.',
            'nama_belakang.required' => 'Nama belakang wajib diisi.',
            'gender.in' => 'Gender harus Laki atau Perempuan.',
        ];
    
        $validator = Validator::make($request->all(), [
            'nama_depan' => 'required|string|max:255',
            'nama_belakang' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'no_hp' => 'nullable|string|max:20',
            'gender' => 'required|in:Laki,P',
        ], $messages);

        if ($validator->fails()) {
            Alert::error('Gagal Registrasi', $validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'nama_depan' => $request->nama_depan,
            'nama_belakang' => $request->nama_belakang,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_type' => 0, 
            'no_hp' => $request->no_hp,
            'gender' => $request->gender,
        ]);
    
        Auth::login($user);
    
        Alert::success('Sukses', 'Registrasi berhasil, silahkan login!');
        return redirect()->route('login');
    }

    public function logout()
    {
        Auth::logout();
        Alert::success('Sukses', 'Berhasil Logout!');
        return redirect()->route('login');
    }
}
