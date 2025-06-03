<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transaction';

    
    protected $fillable = [
        'user_id',
        'sepatu_id',
        'jumlah',
        'total_harga',
        'status',
        'expired_at',
        'snap_token',
        'order_id',
    ];
    
    protected $dates = ['expired_at', 'created_at', 'updated_at'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sepatu()
    {
        return $this->belongsTo(SepatuModel::class, 'sepatu_id');
    }
}
