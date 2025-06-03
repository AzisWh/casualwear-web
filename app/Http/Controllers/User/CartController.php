<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartModel;
use App\Models\SepatuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CartController extends Controller
{
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
}
