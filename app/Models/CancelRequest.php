<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelRequest extends Model
{
    use HasFactory;

    protected $table = 'cancel_request';

    protected $fillable = ['transaction_id', 'reason', 'custom_reason', 'status', 'admin_notes'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
