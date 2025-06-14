<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KategoriModel;
use App\Models\SepatuModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $kategoriId = $request->query('kategori');
        $dataSepatu = SepatuModel::when($kategoriId, function ($query, $kategoriId) {
            return $query->where('id_kat', $kategoriId);
        })->get();
        $dataKategori = KategoriModel::all();
        return view('user.pages.dashboard', compact('dataSepatu', 'dataKategori','kategoriId'));
    }

    public function shop(Request $request)
    {
        $kategoriId = $request->query('kategori');
        $dataSepatu = SepatuModel::when($kategoriId, function ($query, $kategoriId) {
            return $query->where('id_kat', $kategoriId);
        })->get();
        $dataKategori = KategoriModel::all();
        return view('user.pages.shop', compact('dataSepatu', 'dataKategori','kategoriId'));
    }
}
