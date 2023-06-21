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

class DetailDriver extends Model
{
    use HasApiTokens, HasFactory, Notifiable, UUID;

    protected $table = 'detail_drivers';
    public $primaryKey = 'id_detail';
    protected $fillable = [
        'user_id',
        'nik',
        'tempat_lahir',
        'ttl',
        'jk',
        'alamat',
        'foto',
        'kendaraan',
        'plat_no',
        'thn_kendaraan',
        'latitude',
        'longitude',
        'status_akun',
        'status_driver',
        'status',
        'fcm_token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}
