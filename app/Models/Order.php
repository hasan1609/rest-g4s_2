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
use App\Models\DetailDriver;
use App\Models\detailCustomer;
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

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id_user');
    }
    public function detailCustomer()
    {
        return $this->belongsTo(DetailCustomer::class, 'customer_id', 'user_id');
    }

    public function resto()
    {
        return $this->belongsTo(User::class, 'resto_id', 'id_user');
    }
    public function detailResto()
    {
        return $this->belongsTo(DetailResto::class, 'resto_id', 'user_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id_user');
    }
    public function detailDriver()
    {
        return $this->belongsTo(DetailDriver::class, 'driver_id', 'user_id');
    }
    public function countIds()
    {
        // Memecah string data menjadi array menggunakan koma sebagai pemisah
        $idsArray = explode(',', $this->produk_order);
        
        // Menghitung jumlah masing-masing ID dalam array
        $idCounts = count($idsArray);

        return $idCounts;
    }

    
}