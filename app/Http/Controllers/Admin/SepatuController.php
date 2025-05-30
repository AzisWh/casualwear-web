<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriModel;
use App\Models\SepatuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class SepatuController extends Controller
{
    public function index()
    {
        $data = SepatuModel::all();
        $kategori = KategoriModel::all();
        return view('admin.sepatu.index', compact('data','kategori'));
    }

    public function addSepatu(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'size' => 'required|integer',
            'id_kat' => 'required|exists:kategori_sepatu,id',
            'deskripsi' => 'required',
            'stok' => 'required|integer',
            'image_sepatu' => 'required|image|mimes:jpg,jpeg,png',
            'harga_sepatu' => 'required'
        ]);

        try {
            $path = null;
    
            if ($request->hasFile('image_sepatu')) {
                $originalName = time() . '_' . $request->file('image_sepatu')->getClientOriginalName();
                $path = $request->file('image_sepatu')->storeAs('ImageSepatu', $originalName, 'public');
            }
    
            SepatuModel::create([
                'title' => $request->title,
                'size' => $request->size,
                'id_kat' => $request->id_kat,
                'deskripsi' => $request->deskripsi,
                'stok' => $request->stok,
                'image_sepatu' => $path,
                'harga_sepatu' => $request->harga_sepatu
            ]);
    
            Alert::success('Berhasil!', $request->title . ' berhasil ditambahkan.');
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage());
        }

        return redirect()->route('admin.sepatu');
    }

    public function editSepatu(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|nullable|string|max:255',
            'size' => 'sometimes|nullable|integer',
            'id_kat' => 'sometimes|nullable|exists:kategori_sepatu,id',
            'deskripsi' => 'sometimes|nullable|string',
            'stok' => 'sometimes|nullable|integer',
            'image_sepatu' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
            'harga_sepatu' => 'sometimes|nullable'
        ]);

        try {
            $sepatu = SepatuModel::findOrFail($id);

            $data = $request->only(['title', 'size', 'id_kat', 'deskripsi', 'stok', 'image_sepatu', 'harga_sepatu']);

            if ($request->hasFile('image_sepatu')) {
                // Hapus gambar lama jika ada
                if ($sepatu->image && Storage::disk('public')->exists($sepatu->image)) {
                    Storage::disk('public')->delete($sepatu->image);
                }

                $originalName = time() . '_' . $request->file('image_sepatu')->getClientOriginalName();
                $path = $request->file('image_sepatu')->storeAs('ImageSepatu', $originalName, 'public');
                $data['image_sepatu'] = $path;
            }

            $sepatu->update($data);

            Alert::success('Berhasil!', 'Data sepatu berhasil diperbarui.');
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat mengupdate data. ' . $e->getMessage());
        }

        return redirect()->route('admin.sepatu');
    }

    public function delSepatu($id)
    {
        try {
            $sepatu = SepatuModel::findOrFail($id);

            // Hapus gambar jika ada
            if ($sepatu->image && Storage::disk('public')->exists($sepatu->image)) {
                Storage::disk('public')->delete($sepatu->image);
            }

            $sepatu->delete();

            Alert::success('Berhasil!', 'Data sepatu berhasil dihapus.');
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat menghapus data. ' . $e->getMessage());
        }

        return redirect()->route('admin.sepatu');
    }
}
