<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SepatuModel;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class UserCheckoutController extends Controller
{
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
            // Set expired_at to 24 hours 
            $expired_at = Carbon::now()->addHours(24);

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'sepatu_id' => $sepatu->id,
                'jumlah' => $request->jumlah,
                'total_harga' => $total_harga,
                'status' => 'pending',
                'expired_at' => $expired_at,
            ]);

            $sepatu->stok -= $request->jumlah;
            $sepatu->save();

            Alert::success('Berhasil', 'Checkout berhasil! Silakan selesaikan pembayaran dalam 24 jam.');
            return redirect()->route('user.checkout.index');
        } catch (Exception $e) {
            Alert::error('Error', 'Gagal melakukan checkout: ' . $e->getMessage());
            return back();
        }
    }
}
