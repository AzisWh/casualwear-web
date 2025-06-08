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

class CartController extends Controller
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
        $cartItems = CartModel::where('user_id', Auth::id())->with('sepatu')->get();
        return view('user.pages.cart', compact('cartItems'));
    }
    
    public function addToCart(Request $request, SepatuModel $sepatu)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1|max:' . $sepatu->stok,
        ]);

        $jumlah = $request->jumlah;
        $total = $jumlah * $sepatu->harga_sepatu;

        try {

            CartModel::create([
                'user_id' => Auth::id(),
                'sepatu_id' => $sepatu->id,
                'jumlah' => $jumlah,
                'total_harga' => $total,
            ]);

            Alert::success('Berhasil!', 'Produk ditambahkan ke keranjang.');

        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function removeCart(CartModel $cart)
    {
        try {

            $cart->delete();

            Alert::success('Berhasil!', 'Produk berhasil dihapus dari keranjang.');

        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat menghapus: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function checkoutFromCart(Request $request, $id)
    {
        try {
            $cartItem = CartModel::where('id', $id)
                ->where('user_id', Auth::id())
                ->with('sepatu')
                ->firstOrFail();

            $total_harga = $cartItem->total_harga; 
            $expired_at = Carbon::now()->addHours(24); 

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'sepatu_id' => $cartItem->sepatu_id,
                'jumlah' => $cartItem->jumlah,
                'total_harga' => $total_harga,
                'status' => 'pending',
                'expired_at' => $expired_at,
                'order_id' => 'ORDER-' . time(),
            ]);

            $item_details = [
                [
                    'id' => $cartItem->sepatu_id,
                    'price' => $cartItem->sepatu->harga_sepatu,
                    'quantity' => $cartItem->jumlah,
                    'name' => $cartItem->sepatu->title,
                ]
            ];

            $transaction_details = [
                'order_id' => $transaction->order_id,
                'gross_amount' => $total_harga,
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
                'callbacks' => ['finish' => route('user.checkout.index')],
                'expiry' => ['start_time' => Carbon::now()->format('Y-m-d H:i:s O'), 'duration' => 24, 'unit' => 'hours'],
                'custom_field1' => $transaction->id,
            ];

            $snapToken = Snap::getSnapToken($midtrans_params);
            if (!$snapToken) {
                Log::error('Gagal menghasilkan snap token', ['params' => $midtrans_params]);
                throw new Exception('Gagal menghasilkan snap token dari Midtrans.');
            }

            $transaction->snap_token = $snapToken;
            $transaction->save();

            $cartItem->delete(); 

            Alert::success('Berhasil!', 'Berhasil memindahkan item ke halaman checkout. Silakan selesaikan pembayaran.');
            return redirect()->route('user.checkout.index')->with('snapToken', $snapToken);

        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->delete();
            }

            Log::error('Error during checkout from cart: ' . $e->getMessage());
            Alert::error('Error', 'Gagal memproses checkout: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
