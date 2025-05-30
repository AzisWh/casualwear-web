<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SepatuModel extends Model
{
    use HasFactory;

    protected $table = 'sepatu';
    protected $fillable = [
        'title',
        'size',
        'id_kat',
        'image_sepatu',
        'deskripsi',
        'stok',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriModel::class,'id_kat');
    }
}
