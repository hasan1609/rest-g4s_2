<?php

namespace App\Models;

use App\Traits\UUID;
use App\Models\DetailResto;
use App\Models\DetailDriver;
use App\Models\DetailCustomer;
use App\Models\Saldo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UUID;

    protected $guard = 'user';
    protected $table = 'users';
    public $primaryKey = 'id_user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'email',
        'tlp',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function detailResto()
    {
        return $this->hasOne(DetailResto::class, 'user_id');
    }

    public function detailCustomer()
    {
        return $this->hasOne(DetailCustomer::class, 'user_id');
    }

    public function detailDriver()
    {
        return $this->hasOne(DetailDriver::class, 'user_id');
    }
    
    public function saldo()
    {
        return $this->hasOne(Saldo::class, 'user_id');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'user_id');
    }

    public function routeNotificationForFcm()
    {
        return $this->fcm;
    }
}
