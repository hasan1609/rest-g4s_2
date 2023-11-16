<?php

namespace App\Models;

use App\Models\User;
use App\Models\Produk;
use App\Models\DetailResto;
use App\Traits\UUID;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class Cart extends Model
{
    use HasApiTokens, HasFactory, Notifiable, UUID;

    protected $table = 'carts';
    public $primaryKey = 'id_cart';
    protected $fillable = [
        'user_id',
        'produk_id',
        'toko_id',
        'jumlah',
        'total',
        'catatan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id_produk');
    }

    public function resto()
    {
        return $this->belongsTo(DetailResto::class, 'toko_id', 'user_id');
    }
}
