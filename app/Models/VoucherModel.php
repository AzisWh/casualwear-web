<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherModel extends Model
{
    use HasFactory;

    protected $table = 'voucherlist';

    protected $fillable = [
        'code',
        'discount_value',
        'discount_type',
        'start_date',
        'end_date',
        'max_usage',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];
}
