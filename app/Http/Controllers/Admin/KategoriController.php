<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriModel;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class KategoriController extends Controller
{
    public function index()
    {
        $data = KategoriModel::all();
        return view('admin.kategori.index', compact('data'));
    }

    public function addKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|unique:kategori_sepatu,nama_kategori|string|max:255',
        ]);

        try{
            KategoriModel::create(['nama_kategori' => $request->nama_kategori]);
            Alert::success('Data Kategori ' . $request->nama_kategori . ' Berhasil Ditambahkan');
        }catch(\Exception $e){
            Alert::error('Gagal!', 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage());
        }

        return redirect()->route('admin.kategori');
    }

    public function editKategori(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_sepatu,nama_kategori,' . $id,
        ]);

        try {
            $data = KategoriModel::findOrFail($id);
            $data->update([
                'nama_kategori' => $request->nama_kategori,
            ]);
    
            Alert::success('Berhasil, ' . $request->nama_kategori . ' berhasil terupdate.');
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat mengupdate data. ' . $e->getMessage());
        }

        return redirect()->route('admin.kategori');
    }

    public function delKategori($id)
    {
        try {
            $data = KategoriModel::findOrFail($id);
        
            if ($data->sepatu()->exists()) {
                Alert::info('Perhatian!', 'Data Kategori ini tidak dapat dihapus karena masih digunakan oleh data Sepatu.');
            } else {
                $data->delete();
                Alert::success('Berhasil!', 'Data Kategori berhasil dihapus.');
            }
        } catch (\Exception $e) {
            Alert::error('Gagal!', 'Terjadi kesalahan saat menghapus data. ' . $e->getMessage());
        }

        return redirect()->route('admin.kategori');
    }
}
