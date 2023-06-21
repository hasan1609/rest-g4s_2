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

class DetailCustomer extends Model
{
    use HasApiTokens, HasFactory, Notifiable, UUID;

    protected $table = 'detail_customers';
    public $primaryKey = 'id_detail';
    protected $fillable = [
        'user_id',
        'alamat',
        'latitude',
        'longitude',
        'foto',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

}
