<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CancelRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CancelCheckout extends Controller
{
    public function cancelRequest(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $user = Auth::user();

            if ($transaction->user_id !== $user->id) {
                throw new \Exception('Anda tidak memiliki izin untuk membatalkan transaksi ini.');
            }

            if ($transaction->shipping_status === null) {
                $transaction->status = 'cancelled';
                $transaction->shipping_status = 'cancelled';
                $transaction->save();

                Alert::success('Berhasil!', 'Transaksi berhasil dibatalkan.');
            } elseif ($transaction->shipping_status === 'processed') {
                $reason = $request->input('reason');
                $customReason = $request->input('custom_reason');

                if (!$reason && !$customReason) {
                    throw new \Exception('Silakan pilih alasan atau masukkan alasan khusus.');
                }

                CancelRequest::create([
                    'transaction_id' => $id,
                    'reason' => $reason,
                    'custom_reason' => $customReason,
                    'status' => 'pending',
                ]);

                Alert::info('Permintaan Terkirim!', 'Permintaan pembatalan telah dikirim ke admin untuk persetujuan.');
            } else {
                throw new \Exception('Transaksi tidak dapat dibatalkan karena status pengiriman sudah di luar null atau processed.');
            }

            return redirect()->route('user.checkout.index');
        } catch (\Exception $e) {
            Alert::error('Gagal!', $e->getMessage());
            return redirect()->back();
        }
    }
}
