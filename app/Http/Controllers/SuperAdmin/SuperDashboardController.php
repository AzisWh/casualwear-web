<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperDashboardController extends Controller
{
    public function index()
    {
        $transaksiPerBulan = Transaction::select(
            DB::raw('MONTH(transaction.created_at) as bulan'),
            'sepatu.title as shoe_name',
            'transaction.order_id as invoice',
            DB::raw('COUNT(*) as total')
        )
        ->join('sepatu', 'transaction.sepatu_id', '=', 'sepatu.id')
        ->groupBy('bulan', 'shoe_name', 'invoice')
        ->orderBy('bulan')
        ->get();   

        Log::info('Transaksi per bulan:', $transaksiPerBulan->toArray());
        return view('superadmin.dashboard.index', compact('transaksiPerBulan'));
    }
}
