<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartModel;
use App\Models\City;
use App\Models\Province;
use App\Models\SepatuModel;
use App\Models\Transaction;
use App\Models\VoucherModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        try {
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$clientKey = config('services.midtrans.client_key');
            Config::$isProduction = config('services.midtrans.is_production', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;
    
            if (empty(Config::$serverKey) || empty(Config::$clientKey)) {
                throw new \Exception('Server Key atau Client Key tidak ditemukan. Periksa konfigurasi.');
            }
        } catch (\Exception $e) {
            Log::error('Midtrans Init Error', ['message' => $e->getMessage()]);
            abort(500, 'Midtrans Config Error');
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
                'voucher_code' => 'nullable|string',
            ]);

            $sepatu = SepatuModel::findOrFail($sepatu_id);

            if ($request->jumlah > $sepatu->stok) {
                Alert::error('Error', 'Jumlah melebihi stok yang tersedia.');
                return back();
            }

            $total_harga = $sepatu->harga_sepatu * $request->jumlah;
            $discount = 0;
            $voucher_id = null;

            if ($request->voucher_code) {
                $voucher = VoucherModel::where('code', $request->voucher_code)
                    ->where('is_active', true)
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->where('max_usage', '>', DB::raw('used_count'))
                    ->first();

                if ($voucher) {
                    if ($voucher->discount_type === 'percentage') {
                        $discount = $total_harga * ($voucher->discount_value / 100);
                    } else {
                        $discount = min($voucher->discount_value, $total_harga);
                    }
                    $total_harga -= $discount;
                    $voucher->increment('used_count');
                    $voucher_id = $voucher->id;
                } else {
                    Alert::warning('Peringatan', 'Kode voucher tidak valid atau kadaluarsa.');
                }
            }

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
                'discount' => $discount,
                'voucher_id' => $voucher_id,
                'status' => 'pending',
                'shipping_status' => null,
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

            if ($discount > 0) {
                $item_details[] = [
                    'id' => 'DISCOUNT',
                    'price' => -$discount,
                    'quantity' => 1,
                    'name' => 'Diskon (' . $request->voucher_code . ')',
                ];
            }

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

            Alert::success('Berhasil', 'Checkout berhasil! Silakan selesaikan pembayaran dalam 24 jam.');
            return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);

        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->delete();
                if ($voucher) {
                    $voucher->decrement('used_count');
                }
            }

            Alert::error('Error', 'Gagal melakukan checkout: ' . $e->getMessage());
            return back();
        }
    }

    public function checkVoucher(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $voucher = VoucherModel::where('code', $request->code)
            ->where('is_active', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->where('max_usage', '>', DB::raw('used_count'))
            ->first();

        if ($voucher) {
            return response()->json([
                'success' => true,
                'message' => 'Kode voucher ' . $request->code . ' diterapkan!',
                'discount_value' => $voucher->discount_value,
                'discount_type' => $voucher->discount_type
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Kode voucher tidak valid atau kadaluarsa.']);
    }

    public function detail($id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->with('sepatu')->findOrFail($id);
        $provinces = Province::pluck('name', 'province_id');
        return view('user.pages.checkout_detail', compact('transaction', 'provinces'));
    }

    public function getCities($province_id)
    {
        $cities = Cache::remember("cities_province_{$province_id}", now()->addHours(24), function () use ($province_id) {
            return City::where('province_id', $province_id)->pluck('name', 'city_id');
        });
        return response()->json($cities);
    }

    public function calculateShipping(Request $request)
    {
        try {
            Log::info('Hit calculateShipping (new RajaOngkir API)', [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            $request->validate([
                'origin' => 'required|integer',
                'destination' => 'required|integer',
                'weight' => 'required|integer|min:1', 
                'courier' => 'required|string',
                'price' => 'nullable|string|in:lowest,highest',
            ]);

            $postData = http_build_query([
                'origin' => $request->origin,
                'destination' => $request->destination,
                'weight' => $request->weight,
                'courier' => $request->courier,
                'price' => $request->price ?? 'lowest'
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'key: ' . env('KOMERCE_API_KEY'),
                    'Content-Type: application/x-www-form-urlencoded'
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('Komerce RajaOngkir response', [
                'http_code' => $httpCode,
                'response' => $response
            ]);

            if (!$response) {
                return response()->json(['error' => 'CURL error or empty response'], 500);
            }

            $decoded = json_decode($response, true);

            if ($httpCode === 200 && isset($decoded['data'])) {
                return response()->json($decoded['data']);
            } else {
                return response()->json([
                    'error' => 'API call failed',
                    'details' => $decoded
                ], $httpCode);
            }
        } catch (\Exception $e) {
            Log::error('Error in calculateShipping:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    public function saveShipping(Request $request, $id)
    {
        $request->validate([
            'destination' => 'required',
            'courier' => 'required',
            'shipping_cost' => 'required|numeric',
            'service' => 'required',
            'alamat' => 'required|string',
            'deskripsi_alamat' => 'nullable|string',
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
            'alamat' => $request->alamat,
            'deskripsi_alamat' => $request->deskripsi_alamat,
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

    public function updateAddress(Request $request, $id)
    {
        try{
            $request->validate([
                'destination_province' => 'sometimes|exists:provinces,province_id',
                'destination' => 'sometimes|exists:cities,city_id',
                'alamat' => 'sometimes|string',
                'deskripsi_alamat' => 'nullable|string',
            ]);
    
            $user = Auth::user();
            $user->update([
                'asal_provinsi_id' => $request->destination_province,
                'asal_kota_id' => $request->destination,
                'alamat_tinggal' => $request->alamat,
                'deskripsi_alamat' => $request->deskripsi_alamat,
            ]);
    
            Alert::success('Berhasil', 'Alamat profil berhasil diperbarui!');
            return redirect()->back();
        }catch(Exception $e) {
            Log::error('Gagal memperbarui alamat pengiriman', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            Alert::error('Error', 'Gagal memperbarui alamat pengiriman: ' . $e->getMessage());
           return redirect()->back();
        }
    }

    // public function notification(Request $request)
    // {
    //     Log::info('Midtrans Notification Received', $request->all());

    //     try {
    //         DB::beginTransaction();
    //         $notif = new \Midtrans\Notification();

    //         $order_id = $notif->order_id;
    //         $transactions = Transaction::where('order_id', $order_id)->get();

    //         if ($transactions->isEmpty()) {
    //             Log::error('Transaksi tidak ditemukan', ['order_id' => $order_id]);
    //             return response()->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
    //         }

    //         $status = $notif->transaction_status;
    //         $fraud = $notif->fraud_status;

    //         $new_status = 'pending';
    //         if ($status == 'capture') {
    //             if ($fraud == 'challenge') {
    //                 $new_status = 'pending';
    //             } elseif ($fraud == 'accept') {
    //                 $new_status = 'success';
    //             }
    //         } elseif ($status == 'settlement') {
    //             $new_status = 'success';
    //         } elseif ($status == 'pending') {
    //             $new_status = 'pending';
    //         } elseif ($status == 'deny' || $status == 'cancel') {
    //             $new_status = 'failed';
    //         } elseif ($status == 'expire') {
    //             $new_status = 'expired';
    //         }

    //         foreach ($transactions as $transaction) {
    //             $transaction->status = $new_status;
    //             $transaction->save();
                
    //             if ($new_status === 'success') {
    //                 $transaction->shipping_status = 'processed';
    //             }
    //             $transaction->save();

    //             if ($transaction->status == 'success') {
    //                 $sepatu = SepatuModel::findOrFail($transaction->sepatu_id);
    //                 if ($transaction->jumlah > $sepatu->stok) {
    //                     Log::error('Stok tidak cukup saat pembayaran berhasil', [
    //                         'sepatu_id' => $sepatu->id,
    //                         'stok' => $sepatu->stok,
    //                         'jumlah' => $transaction->jumlah,
    //                     ]);
    //                     $transaction->status = 'failed';
    //                     $transaction->shipping_status = null;
    //                     $transaction->save();
    //                     continue;
    //                 }

    //                 $sepatu->stok -= $transaction->jumlah;
    //                 $sepatu->save();
    //                 Log::info('Stok dikurangi', ['sepatu_id' => $sepatu->id, 'new_stok' => $sepatu->stok]);
    //             }
    //         }
    //         DB::commit();

    //         return response()->json(['status' => 'success', 'message' => 'Notification processed'], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Gagal memproses notifikasi Midtrans', [
    //             'error' => $e->getMessage(),
    //             'request' => $request->all(),
    //         ]);
    //         return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
    //     }
    // }    

    public function notification(Request $request)
    {
        Log::info('Midtrans Notification Received', $request->all());

        file_put_contents(storage_path('logs/midtrans_raw_input.txt'), json_encode($request->all(), JSON_PRETTY_PRINT));

        try {
            DB::beginTransaction();

            try {
                $notif = new \Midtrans\Notification();
            } catch (\Exception $e) {
                Log::error('Gagal inisialisasi Midtrans Notification', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal inisialisasi notifikasi Midtrans',
                ], 500);
            }

            $order_id = $notif->order_id;
            $transactions = Transaction::where('order_id', $order_id)->get();

            if ($transactions->isEmpty()) {
                Log::error('Transaksi tidak ditemukan', ['order_id' => $order_id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            $status = $notif->transaction_status;
            $fraud = $notif->fraud_status;

            $new_status = match ($status) {
                'capture' => $fraud == 'challenge' ? 'pending' : 'success',
                'settlement' => 'success',
                'pending' => 'pending',
                'deny', 'cancel' => 'failed',
                'expire' => 'expired',
                default => 'pending',
            };

            foreach ($transactions as $transaction) {
                $transaction->status = $new_status;

                if ($new_status === 'success') {
                    $transaction->shipping_status = 'processed';

                    $sepatu = SepatuModel::findOrFail($transaction->sepatu_id);
                    if ($transaction->jumlah > $sepatu->stok) {
                        Log::error('Stok tidak cukup saat pembayaran berhasil', [
                            'sepatu_id' => $sepatu->id,
                            'stok' => $sepatu->stok,
                            'jumlah' => $transaction->jumlah,
                        ]);

                        $transaction->status = 'failed';
                        $transaction->shipping_status = null;
                        $transaction->save();
                        continue;
                    }

                    $sepatu->stok -= $transaction->jumlah;
                    $sepatu->save();

                    Log::info('Stok berhasil dikurangi', [
                        'sepatu_id' => $sepatu->id,
                        'stok_baru' => $sepatu->stok,
                    ]);
                }

                $transaction->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi berhasil diproses',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal memproses notifikasi Midtrans', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses notifikasi',
            ], 500);
        }
    }


    

    public function expire($transactionId)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($transactionId);
        if ($transaction->status === 'pending' && \Carbon\Carbon::parse($transaction->expired_at, 'Asia/Jakarta')->isPast()) {
            $transaction->update(['status' => 'failed']);
            return redirect()->back()->with('success', 'Status transaksi telah diperbarui menjadi kadaluarsa.');
        }
        return redirect()->back()->with('error', 'Transaksi tidak dapat diperbarui atau belum kadaluarsa.');
    }

    // public function reconfirmCheckout($id)
    // {
    //     try {
    //         $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
    //         $sepatu = $transaction->sepatu;

    //         if ($sepatu->stok < $transaction->jumlah) {
    //             Alert::error('Error', 'Stok tidak mencukupi untuk konfirmasi ulang.');
    //             return redirect()->back();
    //         }

    //         $expired_at = Carbon::now()->addHours(24);
    //         $transaction->update([
    //             'status' => 'pending',
    //             'expired_at' => $expired_at,
    //             'snap_token' => null, 
    //         ]);

    //         $transaction_details = [
    //             'order_id' => $transaction->order_id,
    //             'gross_amount' => $transaction->total_harga,
    //         ];

    //         $item_details = [
    //             [
    //                 'id' => $sepatu->id,
    //                 'price' => $sepatu->harga_sepatu,
    //                 'quantity' => $transaction->jumlah,
    //                 'name' => $sepatu->title,
    //             ],
    //         ];

    //         if ($transaction->discount > 0) {
    //             $item_details[] = [
    //                 'id' => 'DISCOUNT',
    //                 'price' => -$transaction->discount,
    //                 'quantity' => 1,
    //                 'name' => 'Diskon (' . ($transaction->voucher_id ? VoucherModel::find($transaction->voucher_id)->code : '') . ')',
    //             ];
    //         }

    //         $customer_details = [
    //             'first_name' => Auth::user()->nama_depan,
    //             'last_name' => Auth::user()->nama_belakang,
    //             'email' => Auth::user()->email,
    //             'phone' => Auth::user()->no_hp,
    //         ];

    //         $midtrans_params = [
    //             'transaction_details' => $transaction_details,
    //             'item_details' => $item_details,
    //             'customer_details' => $customer_details,
    //             'enabled_payments' => ['gopay', 'shopeepay', 'bank_transfer', 'qris'],
    //             'callbacks' => [
    //                 'finish' => route('user.checkout.index'),
    //             ],
    //             'expiry' => [
    //                 'start_time' => Carbon::now()->format('Y-m-d H:i:s O'),
    //                 'duration' => 24,
    //                 'unit' => 'hours',
    //             ],
    //             'custom_field1' => $transaction->id,
    //         ];

    //         $snapToken = Snap::getSnapToken($midtrans_params);
    //         $transaction->snap_token = $snapToken;
    //         $transaction->save();

    //         Alert::success('Berhasil', 'Checkout dikonfirmasi ulang! Silakan selesaikan pembayaran dalam 24 jam.');
    //         return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);

    //     } catch (Exception $e) {
    //         Alert::error('Error', 'Gagal mengkonfirmasi ulang checkout: ' . $e->getMessage());
    //         return redirect()->back();
    //     }
    // }
}
