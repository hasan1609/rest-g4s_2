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

class OrderResto extends Model
{
    use HasApiTokens, HasFactory, Notifiable, UUID;

    protected $table = 'order_restos';
    public $primaryKey = 'id_order_resto';
    protected $fillable = [
        'id_order_resto',
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
        return $this->belongsTo(Detailresto::class, 'toko_id', 'user_id');
    }
}
