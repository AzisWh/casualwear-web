<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class MonitorTraksaksiController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['user', 'sepatu', 'voucher'])->get();
        return view('admin.transaksi.transaksi', compact('transactions'));
    }

    public function destroy($id)
    {
        try{
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();
    
            Alert::success('Berhasil!', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat menghapus data. ' . $e->getMessage());
        }


        return redirect()->route('admin.transactions.index');
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'sepatu', 'voucher'])->findOrFail($id);
        return view('admin.transaksi.transaksi-detail', compact('transaction'))->render();
    }
}
