<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriModel extends Model
{
    use HasFactory;

    protected $table = 'kategori_sepatu';

    protected $fillable = ['nama_kategori'];

    public function sepatu()
    {
        return $this->hasMany(SepatuModel::class, 'id_kat');
    }
}
