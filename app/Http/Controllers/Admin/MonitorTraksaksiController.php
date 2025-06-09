<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CancelRequest;
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
