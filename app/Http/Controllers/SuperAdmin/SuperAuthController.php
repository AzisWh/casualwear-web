<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class SuperAuthController extends Controller
{
   public function index()
   {
        return view ('superadmin.auth.index');
   }

   public function login(Request $request)
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

        if (Auth::guard('superadmin')->attempt($credentials)) 
        {
            Alert::success('success','Berhasil Login');
            return redirect()->route('dashboard.super');
        }

        Alert::error('Gagal', 'Email atau password salah.');
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
   }

   public function logout()
    {
        Auth::logout();
        Alert::success('Sukses', 'Berhasil Logout!');
        return redirect()->route('superadmin.login');
    }
}
