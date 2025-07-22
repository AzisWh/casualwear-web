<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CancelRequest;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class MonitorTraksaksiController extends Controller
{
    public function index(Request $request)
    {
        $filterdata = Transaction::with(['user', 'sepatu', 'voucher']);

        // filter tanggal
        if ($request->filled('tanggal')) {
            $tanggal = Carbon::parse($request->tanggal)->startOfDay();
            $filterdata->whereDate('created_at', $tanggal);
        } 
        // bulan n taun
        elseif ($request->filled('bulan') || $request->filled('tahun')) {
            if ($request->filled('bulan')) {
                $filterdata->whereMonth('created_at', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $filterdata->whereYear('created_at', $request->tahun);
            }
        }
        // minggu ini
        elseif ($request->filled('filter') && $request->filter === 'this_week') {
            $filterdata->whereBetween('created_at', [
                Carbon::now()->startOfWeek(Carbon::MONDAY),
                Carbon::now()->endOfWeek(Carbon::SUNDAY),
            ]);
        }

        $transactions = $filterdata->paginate(5)->appends($request->query());
        
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

    public function updateShippingStatus(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->shipping_status = $request->input('shipping_status');
            $transaction->save();

            Alert::success('Berhasil!', 'Status pengiriman berhasil diperbarui.');
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat memperbarui status pengiriman. ' . $e->getMessage());
        }

        return redirect()->route('admin.transactions.index');
    }

    public function approveCancellation(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            if ($transaction->cancellation_status === 'pending_cancellation') {
                $transaction->status = 'cancelled';
                $transaction->shipping_status = 'cancelled';
                $transaction->cancellation_status = 'approved';
                $transaction->save();

                Alert::success('Berhasil!', 'Pembatalan transaksi disetujui.');
            } else {
                throw new \Exception('Tidak ada permintaan pembatalan yang tertunda.');
            }
            return redirect()->route('admin.transactions.index');
        } catch (\Exception $e) {
            Alert::error('Gagal!', $e->getMessage());
            return redirect()->back();
        }
    }

    public function rejectCancellation(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            if ($transaction->cancellation_status === 'pending_cancellation') {
                $transaction->cancellation_status = 'rejected';
                $transaction->save();

                Alert::warning('Ditolak!', 'Pembatalan transaksi ditolak.');
            } else {
                throw new \Exception('Tidak ada permintaan pembatalan yang tertunda.');
            }
            return redirect()->route('admin.transactions.index');
        } catch (\Exception $e) {
            Alert::error('Gagal!', $e->getMessage());
            return redirect()->back();
        }
    }
}
