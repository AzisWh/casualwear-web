<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KategoriModel;
use App\Models\SepatuModel;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $filterfavorit = $request->query('filterfavorit');
        $popularSepatuIds = [];
        
        if ($filterfavorit === 'sering' || $filterfavorit === 'jarang') {
            $query = DB::table('transaction')
                ->select('sepatu_id', DB::raw('COUNT(*) as total'))
                ->where('status', 'success')
                ->groupBy('sepatu_id')
                ->orderBy('total', $filterfavorit === 'sering' ? 'DESC' : 'ASC');
    
            if ($filterfavorit === 'sering') {
                $query->havingRaw('COUNT(*) >= 3');
            } else {
                $query->havingRaw('COUNT(*) < 3');
            }
            
            $popularSepatuIds = $query->pluck('sepatu_id')->toArray();
        }
    
        $dataSepatu = SepatuModel::query()
            ->when($kategoriId, function ($query, $kategoriId) {
                return $query->where('id_kat', $kategoriId);
            })
            ->when(!empty($popularSepatuIds), function ($query) use ($popularSepatuIds) {
                return $query->whereIn('id', $popularSepatuIds);
            })
            ->get();
        
        $dataKategori = KategoriModel::all();
        
        return view('user.pages.shop', compact('dataSepatu', 'dataKategori', 'kategoriId', 'filterfavorit'));
    }
}
