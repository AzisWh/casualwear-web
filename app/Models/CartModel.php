<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartModel extends Model
{
    use HasFactory;

    protected $table = 'user_cart';

    protected $fillable = [
        'user_id',
        'sepatu_id',
        'jumlah',
        'total_harga',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sepatu()
    {
        return $this->belongsTo(SepatuModel::class, 'sepatu_id');
    }
}
