<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;
use App\Models\DetailResto;
use App\Traits\UUID;

class Booking extends Model
{
    use HasApiTokens, HasFactory, Notifiable, UUID;

    protected $table = 'bookings';
    public $primaryKey = 'id_booking';
    protected $fillable = [
        'id_booking',
        'customer_id',
        'resto_id',
        'produk_order',
        'ongkos_kirim',
        'biaya_pesanan',
        'total',
        'kategori',
        'alamat_dari',
        'longitude_dari',
        'latitude_dari',
        'alamat_tujuan',
        'longitude_tujuan',
        'latitude_tujuan'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id_user');
    }

    public function resto()
    {
        return $this->belongsTo(User::class, 'resto_id', 'id_user');
    }
    public function detailResto()
    {
        return $this->belongsTo(DetailResto::class, 'resto_id', 'user_id');
    }
    
}