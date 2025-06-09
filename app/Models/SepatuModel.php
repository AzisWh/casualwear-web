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
        'harga_sepatu',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriModel::class,'id_kat');
    }

    public function carts()
    {
        return $this->hasMany(CartModel::class, 'sepatu_id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'sepatu_id');
    }

    public function reviews()
    {
        return $this->hasMany(ReviewModel::class);
    }
}
