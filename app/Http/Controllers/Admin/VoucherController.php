<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoucherModel;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class VoucherController extends Controller
{
    public function index()
    {
        $data = VoucherModel::all();
        return view('admin.voucher.index', compact('data'));
    }

    public function addVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:voucherlist,code',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_usage' => 'nullable|integer|min:0',
        ]);

        try{
            VoucherModel::create([
                'code' => $request->code,
                'discount_value' => $request->discount_value,
                'discount_type' => $request->discount_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'max_usage' => $request->max_usage,
                'is_active' => true,
            ]);

            Alert::success('Berhasil! Voucher ', $request->code . ' berhasil ditambahkan.');
        }catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage());
        }
        return redirect()->route('admin.voucher');
    }

    public function editVoucher(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:voucherlist,code,' . $id,
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_usage' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        try{
            $voucher = VoucherModel::findOrFail($id);
            $voucher->update([
                'code' => $request->code,
                'discount_value' => $request->discount_value,
                'discount_type' => $request->discount_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'max_usage' => $request->max_usage,
                'is_active' => $request->is_active,
            ]);

            Alert::success('Berhasil! Voucher ', $request->code . ' berhasil diperbarui.');
        }catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat update data. ' . $e->getMessage());
        }
        return redirect()->route('admin.voucher');
    }

    public function delVoucher($id)
    {
        try {
            $voucher = VoucherModel::findOrFail($id);

            $voucher->delete();

            Alert::success('Berhasil!', 'Data voucher berhasil dihapus.');
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat menghapus data. ' . $e->getMessage());
        }

        return redirect()->route('admin.voucher');
    }
}
