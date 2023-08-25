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

class Order extends Model
{
    use HasApiTokens, HasFactory, Notifiable, UUID;

    protected $table = 'orders';
    public $primaryKey = 'id_order';
    protected $fillable = [
        'id_order',
        'customer_id',
        'resto_id',
        'driver_id',
        'produk_order',
        'ongkos_kirim',
        'biaya_pesanan',
        'total',
        'status',
        'alamat_tujuan',
        'longitude_tujuan',
        'latitude_tujuan'
    ];
    
}