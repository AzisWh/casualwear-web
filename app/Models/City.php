<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    protected $fillable = ['city_id', 'province_id', 'name', 'type', 'postal_code'];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
}
