<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckShipping extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shippingStatusFilter = $request->input('shipping_status');

        $query = Transaction::where('user_id', $user->id)
            ->with(['sepatu', 'user']);

        if ($shippingStatusFilter) {
            $query->where('shipping_status', $shippingStatusFilter);
        }

        $transactions = $query->get();

        return view('user.pages.shipping', compact('transactions', 'shippingStatusFilter'));
    }
}
