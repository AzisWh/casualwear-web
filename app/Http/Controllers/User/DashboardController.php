<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KategoriModel;
use App\Models\SepatuModel;
use App\Models\Transaction;
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

        $bestSellers = Transaction::select('sepatu_id')
        ->selectRaw('SUM(jumlah) as total_sold')
        ->groupBy('sepatu_id')
        ->orderByDesc('total_sold')
        ->limit(2)
        ->with('sepatu') 
        ->get()
        ->filter(function ($transaction) {
            return $transaction->sepatu !== null; 
        });
        return view('user.pages.dashboard', compact('dataSepatu', 'dataKategori','kategoriId','bestSellers'));
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
