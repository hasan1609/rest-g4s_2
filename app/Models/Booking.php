<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;
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
        'status',
        'produk_order',
        'ongkos_kirim',
        'biaya_pesanan',
        'total'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id_user');
    }

    public function resto()
    {
        return $this->belongsTo(User::class, 'resto_id', 'id_user');
    }
    
}