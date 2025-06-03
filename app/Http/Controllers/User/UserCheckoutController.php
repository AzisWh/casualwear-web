<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartModel;
use App\Models\SepatuModel;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;

class UserCheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        if (empty(Config::$serverKey) || empty(Config::$clientKey)) {
            throw new Exception('Server Key atau Client Key tidak ditemukan. Periksa konfigurasi di config/services.php dan .env.');
        }
    }
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())->with('sepatu')->latest()->get();
        return view('user.pages.checkout', compact('transactions'));
    }

    public function store(Request $request, $sepatu_id = null)
    {
        try {
            // Checkout langsung (dengan sepatu_id)
            if ($sepatu_id) {
                $request->validate([
                    'jumlah' => 'required|integer|min:1',
                ]);

                $sepatu = SepatuModel::findOrFail($sepatu_id);

                if ($request->jumlah > $sepatu->stok) {
                    return redirect()->back()->with('error', 'Jumlah melebihi stok yang tersedia.');
                }

                $total_harga = $sepatu->harga_sepatu * $request->jumlah;
                $expired_at = Carbon::now('Asia/Jakarta')->addHours(24);

                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'sepatu_id' => $sepatu->id,
                    'jumlah' => $request->jumlah,
                    'total_harga' => $total_harga,
                    'status' => 'pending',
                    'expired_at' => $expired_at,
                ]);

                $order_id = 'ORDER-' . $transaction->id . '-' . time();
                $transaction->update(['order_id' => $order_id]);

                $transaction_details = [
                    'order_id' => $order_id,
                    'gross_amount' => $total_harga,
                ];

                $item_details = [
                    [
                        'id' => $sepatu->id,
                        'price' => $sepatu->harga_sepatu,
                        'quantity' => $request->jumlah,
                        'name' => $sepatu->title,
                    ],
                ];

                $customer_details = [
                    'first_name' => Auth::user()->nama_depan,
                    'last_name' => Auth::user()->nama_belakang,
                    'email' => Auth::user()->email,
                    'phone' => Auth::user()->no_hp,
                ];

                $midtrans_params = [
                    'transaction_details' => $transaction_details,
                    'item_details' => $item_details,
                    'customer_details' => $customer_details,
                    'enabled_payments' => ['gopay', 'shopeepay', 'bank_transfer', 'qris'],
                    'callbacks' => [
                        'finish' => route('user.checkout.index'),
                    ],
                    'expiry' => [
                        'start_time' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s O'),
                        'duration' => 24,
                        'unit' => 'hours',
                    ],
                    'custom_field1' => $transaction->id,
                ];

                $snapToken = Snap::getSnapToken($midtrans_params);
                $transaction->update(['snap_token' => $snapToken]);

                return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);
            }

            // Checkout multi-item dari cart
            $selectedItems = $request->input('selected_items', []);
            if (empty($selectedItems)) {
                return redirect()->back()->with('error', 'Pilih setidaknya satu item untuk checkout.');
            }

            $totalAmount = 0;
            $items = [];
            $cartItems = CartModel::whereIn('id', $selectedItems)->where('user_id', Auth::id())->with('sepatu')->get();

            if ($cartItems->isEmpty()) {
                return redirect()->back()->with('error', 'Item yang dipilih tidak valid.');
            }

            $transactions = [];
            $order_id_base = 'ORDER-' . time();
            foreach ($cartItems as $index => $cartItem) {
                $sepatu = $cartItem->sepatu;
                if ($cartItem->jumlah > $sepatu->stok) {
                    return redirect()->back()->with('error', 'Jumlah ' . $sepatu->title . ' melebihi stok yang tersedia.');
                }

                $totalAmount += $cartItem->total_harga;
                $items[] = [
                    'id' => $sepatu->id,
                    'price' => $sepatu->harga_sepatu,
                    'quantity' => $cartItem->jumlah,
                    'name' => $sepatu->title,
                ];

                $expired_at = Carbon::now('Asia/Jakarta')->addHours(24);
                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'sepatu_id' => $sepatu->id,
                    'jumlah' => $cartItem->jumlah,
                    'total_harga' => $cartItem->total_harga,
                    'status' => 'pending',
                    'expired_at' => $expired_at,
                    'order_id' => $order_id_base . '-' . ($index + 1),
                ]);
                $transactions[] = $transaction;
            }

            $transaction_details = [
                'order_id' => $order_id_base,
                'gross_amount' => $totalAmount,
            ];

            $customer_details = [
                'first_name' => Auth::user()->nama_depan,
                'last_name' => Auth::user()->nama_belakang,
                'email' => Auth::user()->email,
                'phone' => Auth::user()->no_hp,
            ];

            $midtrans_params = [
                'transaction_details' => $transaction_details,
                'item_details' => $items,
                'customer_details' => $customer_details,
                'enabled_payments' => ['gopay', 'shopeepay', 'bank_transfer', 'qris'],
                'callbacks' => [
                    'finish' => route('user.checkout.index'),
                ],
                'expiry' => [
                    'start_time' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s O'),
                    'duration' => 24,
                    'unit' => 'hours',
                ],
                'custom_field1' => collect($transactions)->pluck('id')->implode(','),
            ];

            $snapToken = Snap::getSnapToken($midtrans_params);
            $transactions[count($transactions) - 1]->update(['snap_token' => $snapToken]);

            CartModel::whereIn('id', $selectedItems)->delete();

            return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan checkout: ' . $e->getMessage());
        }
    }

    public function notification(Request $request)
    {
        Log::info('Midtrans Notification Received', $request->all());

        try {
            $notif = new \Midtrans\Notification();

            $order_id = $notif->order_id;
            $transactions = Transaction::where('order_id', $order_id)->get();

            if ($transactions->isEmpty()) {
                Log::error('Transaksi tidak ditemukan', ['order_id' => $order_id]);
                return response()->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
            }

            $status = $notif->transaction_status;
            $fraud = $notif->fraud_status;

            $new_status = 'pending';
            if ($status == 'capture') {
                if ($fraud == 'challenge') {
                    $new_status = 'pending';
                } elseif ($fraud == 'accept') {
                    $new_status = 'success';
                }
            } elseif ($status == 'settlement') {
                $new_status = 'success';
            } elseif ($status == 'pending') {
                $new_status = 'pending';
            } elseif ($status == 'deny' || $status == 'cancel') {
                $new_status = 'failed';
            } elseif ($status == 'expire') {
                $new_status = 'expired';
            }

            foreach ($transactions as $transaction) {
                $transaction->status = $new_status;
                $transaction->save();

                if ($transaction->status == 'success') {
                    $sepatu = SepatuModel::findOrFail($transaction->sepatu_id);
                    if ($transaction->jumlah > $sepatu->stok) {
                        Log::error('Stok tidak cukup saat pembayaran berhasil', [
                            'sepatu_id' => $sepatu->id,
                            'stok' => $sepatu->stok,
                            'jumlah' => $transaction->jumlah,
                        ]);
                        $transaction->status = 'failed';
                        $transaction->save();
                        continue;
                    }

                    $sepatu->stok -= $transaction->jumlah;
                    $sepatu->save();
                    Log::info('Stok dikurangi', ['sepatu_id' => $sepatu->id, 'new_stok' => $sepatu->stok]);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Notification processed'], 200);
        } catch (\Exception $e) {
            Log::error('Gagal memproses notifikasi Midtrans', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    public function expire($transactionId)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($transactionId);
        if ($transaction->status === 'pending' && \Carbon\Carbon::parse($transaction->expired_at, 'Asia/Jakarta')->isPast()) {
            $transaction->update(['status' => 'expired']);
            return redirect()->back()->with('success', 'Status transaksi telah diperbarui menjadi kadaluarsa.');
        }
        return redirect()->back()->with('error', 'Transaksi tidak dapat diperbarui atau belum kadaluarsa.');
    }
}
