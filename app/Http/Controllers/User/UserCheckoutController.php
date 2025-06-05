<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartModel;
use App\Models\City;
use App\Models\Province;
use App\Models\SepatuModel;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

    public function store(Request $request, $sepatu_id)
    {
        try {
            $request->validate([
                'jumlah' => 'required|integer|min:1',
            ]);

            $sepatu = SepatuModel::findOrFail($sepatu_id);

            if ($request->jumlah > $sepatu->stok) {
                Alert::error('Error', 'Jumlah melebihi stok yang tersedia.');
                return back();
            }

            $total_harga = $sepatu->harga_sepatu * $request->jumlah;

            if ($total_harga > 99999999.99) {
                Alert::error('Error', 'Total harga melebihi batas maksimum yang diizinkan.');
                return back();
            }

            $expired_at = Carbon::now()->addHours(24);

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
                    'start_time' => Carbon::now()->format('Y-m-d H:i:s O'),
                    'duration' => 24,
                    'unit' => 'hours',
                ],
                'custom_field1' => $transaction->id,
            ];

            $snapToken = Snap::getSnapToken($midtrans_params);
            // if (!$snapToken) {
            //     Log::error('Gagal menghasilkan snap token', ['params' => $midtrans_params]);
            //     throw new Exception('Gagal menghasilkan snap token dari Midtrans.');
            // }
            $transaction->snap_token = $snapToken;
            $transaction->save();

            Alert::success('Berhasil', 'Checkout berhasil! Silakan selesaikan pembayaran dalam 24 jam.');
            return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);

        } catch (Exception $e) {
            // Rollback stok jika gagal
            if (isset($transaction)) {
                $transaction->delete();
            }

            Alert::error('Error', 'Gagal melakukan checkout: ' . $e->getMessage());
            return back();
        }
    }

    public function detail($id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->with('sepatu')->findOrFail($id);
        $provinces = Province::pluck('name', 'province_id');
        return view('user.pages.checkout_detail', compact('transaction', 'provinces'));
    }

    public function getCities($province_id)
    {
        $cities = City::where('province_id', $province_id)->pluck('name', 'city_id');
        return response()->json($cities);
    }

    // public function calculateShipping(Request $request, $id)
    // {
    //     $request->validate([
    //         'destination' => 'required',
    //         'courier' => 'required|in:jne,pos,tiki',
    //     ]);

    //     $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

    //     $origin = 152;
    //     $weight = $request->jumlah * 1000; 

    //     $response = Http::withHeaders([
    //         'key' => env('RAJAONGKIR_API_KEY')
    //     ])->post('https://api.rajaongkir.com/starter/cost', [
    //         'origin' => $origin,
    //         'destination' => $request->destination,
    //         'weight' => $weight,
    //         'courier' => $request->courier,
    //     ]);

    //     if ($response->successful()) {
    //         $costs = $response->json()['rajaongkir']['results'][0]['costs'];
    //         return response()->json($costs);
    //     }

    //     return response()->json(['error' => 'Failed to fetch shipping cost'], 500);
    // }

    public function calculateShipping(Request $request, $id)
    {
        $request->validate([
            'destination' => 'required',
            'courier' => 'required|in:jne,pos,tiki',
        ]);

        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        // Asumsikan kota asal adalah Jakarta Selatan (city_id: 152)
        $origin = 152;
        $weight = $request->jumlah * 1000; // Asumsikan 1 item = 1 kg

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.rajaongkir.com/starter/cost');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'origin' => $origin,
            'destination' => $request->destination,
            'weight' => $weight,
            'courier' => $request->courier,
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . env('RAJAONGKIR_API_KEY'),
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return response()->json(['error' => 'CURL Error: ' . curl_error($ch)], 500);
        }

        $data = json_decode($response, true);
        if (isset($data['rajaongkir']['status']['code']) && $data['rajaongkir']['status']['code'] == 200) {
            $costs = $data['rajaongkir']['results'][0]['costs'];
            return response()->json($costs);
        }

        return response()->json(['error' => 'Failed to fetch shipping cost'], 500);
    }
    
    public function saveShipping(Request $request, $id)
    {
        $request->validate([
            'destination' => 'required',
            'courier' => 'required',
            'shipping_cost' => 'required|numeric',
            'service' => 'required',
        ]);

        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        $origin = 152; // Jakarta Selatan
        $total_harga = $transaction->total_harga + $request->shipping_cost;

        $transaction->update([
            'origin' => $origin,
            'destination' => $request->destination,
            'courier' => $request->courier,
            'shipping_cost' => $request->shipping_cost,
            'service' => $request->service,
            'total_harga' => $total_harga,
        ]);

        $transaction_details = [
            'order_id' => $transaction->order_id,
            'gross_amount' => $total_harga,
        ];

        $item_details = [
            [
                'id' => $transaction->sepatu->id,
                'price' => $transaction->sepatu->harga_sepatu,
                'quantity' => $transaction->jumlah,
                'name' => $transaction->sepatu->title,
            ],
            [
                'id' => 'SHIPPING',
                'price' => $request->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim (' . strtoupper($request->courier) . ' - ' . $request->service . ')',
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
                'start_time' => Carbon::now()->format('Y-m-d H:i:s O'),
                'duration' => 24,
                'unit' => 'hours',
            ],
            'custom_field1' => $transaction->id,
        ];

        $snapToken = Snap::getSnapToken($midtrans_params);
        $transaction->snap_token = $snapToken;
        $transaction->save();

        Alert::success('Berhasil', 'Detail pengiriman disimpan! Silakan selesaikan pembayaran dalam 24 jam.');
        return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);
    }


    // public function store(Request $request, $sepatu_id = null)
    // {
    //     try {
    //         // Checkout langsung (dengan sepatu_id)
    //         if ($sepatu_id) {
    //             $request->validate([
    //                 'jumlah' => 'required|integer|min:1',
    //             ]);

    //             $sepatu = SepatuModel::findOrFail($sepatu_id);

    //             if ($request->jumlah > $sepatu->stok) {
    //                 return redirect()->back()->with('error', 'Jumlah melebihi stok yang tersedia.');
    //             }

    //             $total_harga = $sepatu->harga_sepatu * $request->jumlah;
    //             $expired_at = Carbon::now('Asia/Jakarta')->addHours(24);

    //             $transaction = Transaction::create([
    //                 'user_id' => Auth::id(),
    //                 'sepatu_id' => $sepatu->id,
    //                 'jumlah' => $request->jumlah,
    //                 'total_harga' => $total_harga,
    //                 'status' => 'pending',
    //                 'expired_at' => $expired_at,
    //             ]);

    //             $order_id = 'ORDER-' . $transaction->id . '-' . time();
    //             $transaction->update(['order_id' => $order_id]);

    //             $transaction_details = [
    //                 'order_id' => $order_id,
    //                 'gross_amount' => $total_harga,
    //             ];

    //             $item_details = [
    //                 [
    //                     'id' => $sepatu->id,
    //                     'price' => $sepatu->harga_sepatu,
    //                     'quantity' => $request->jumlah,
    //                     'name' => $sepatu->title,
    //                 ],
    //             ];

    //             $customer_details = [
    //                 'first_name' => Auth::user()->nama_depan,
    //                 'last_name' => Auth::user()->nama_belakang,
    //                 'email' => Auth::user()->email,
    //                 'phone' => Auth::user()->no_hp,
    //             ];

    //             $midtrans_params = [
    //                 'transaction_details' => $transaction_details,
    //                 'item_details' => $item_details,
    //                 'customer_details' => $customer_details,
    //                 'enabled_payments' => ['gopay', 'shopeepay', 'bank_transfer', 'qris'],
    //                 'callbacks' => [
    //                     'finish' => route('user.checkout.index'),
    //                 ],
    //                 'expiry' => [
    //                     'start_time' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s O'),
    //                     'duration' => 24,
    //                     'unit' => 'hours',
    //                 ],
    //                 'custom_field1' => $transaction->id,
    //             ];

    //             $snapToken = Snap::getSnapToken($midtrans_params);
    //             $transaction->update(['snap_token' => $snapToken]);

    //             return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);
    //         }

    //         // Checkout multi-item dari cart
    //         $selectedItems = $request->input('selected_items', []);
    //         if (empty($selectedItems)) {
    //             return redirect()->back()->with('error', 'Pilih setidaknya satu item untuk checkout.');
    //         }

    //         $totalAmount = 0;
    //         $items = [];
    //         $cartItems = CartModel::whereIn('id', $selectedItems)->where('user_id', Auth::id())->with('sepatu')->get();

    //         if ($cartItems->isEmpty()) {
    //             return redirect()->back()->with('error', 'Item yang dipilih tidak valid.');
    //         }

    //         $transactions = [];
    //         $order_id_base = 'ORDER-' . time();
    //         foreach ($cartItems as $index => $cartItem) {
    //             $sepatu = $cartItem->sepatu;
    //             if ($cartItem->jumlah > $sepatu->stok) {
    //                 return redirect()->back()->with('error', 'Jumlah ' . $sepatu->title . ' melebihi stok yang tersedia.');
    //             }

    //             $totalAmount += $cartItem->total_harga;
    //             $items[] = [
    //                 'id' => $sepatu->id,
    //                 'price' => $sepatu->harga_sepatu,
    //                 'quantity' => $cartItem->jumlah,
    //                 'name' => $sepatu->title,
    //             ];

    //             $expired_at = Carbon::now('Asia/Jakarta')->addHours(24);
    //             $transaction = Transaction::create([
    //                 'user_id' => Auth::id(),
    //                 'sepatu_id' => $sepatu->id,
    //                 'jumlah' => $cartItem->jumlah,
    //                 'total_harga' => $cartItem->total_harga,
    //                 'status' => 'pending',
    //                 'expired_at' => $expired_at,
    //                 'order_id' => $order_id_base . '-' . ($index + 1),
    //             ]);
    //             $transactions[] = $transaction;
    //         }

    //         $transaction_details = [
    //             'order_id' => $order_id_base,
    //             'gross_amount' => $totalAmount,
    //         ];

    //         $customer_details = [
    //             'first_name' => Auth::user()->nama_depan,
    //             'last_name' => Auth::user()->nama_belakang,
    //             'email' => Auth::user()->email,
    //             'phone' => Auth::user()->no_hp,
    //         ];

    //         $midtrans_params = [
    //             'transaction_details' => $transaction_details,
    //             'item_details' => $items,
    //             'customer_details' => $customer_details,
    //             'enabled_payments' => ['gopay', 'shopeepay', 'bank_transfer', 'qris'],
    //             'callbacks' => [
    //                 'finish' => route('user.checkout.index'),
    //             ],
    //             'expiry' => [
    //                 'start_time' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s O'),
    //                 'duration' => 24,
    //                 'unit' => 'hours',
    //             ],
    //             'custom_field1' => collect($transactions)->pluck('id')->implode(','),
    //         ];

    //         $snapToken = Snap::getSnapToken($midtrans_params);
    //         $transactions[count($transactions) - 1]->update(['snap_token' => $snapToken]);

    //         // Hapus item dari cart setelah transaksi berhasil dibuat
    //         CartModel::whereIn('id', $selectedItems)->delete();

    //         return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Gagal melakukan checkout: ' . $e->getMessage());
    //     }
    // }

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
