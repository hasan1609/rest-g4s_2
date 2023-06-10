<?php

namespace App\Models;

use App\Traits\UUID;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory, UUID;

    protected $table = 'produks';
    public $primaryKey = 'id_produk';
    protected $fillable = [
        'user_id',
        'nama_produk',
        'harga',
        'keterangan',
        'foto_produk',
        'rating',
        'terjual',
        'status',
        'kategori',
        'rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}