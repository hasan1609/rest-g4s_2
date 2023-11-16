<?php

namespace App\Models;

use App\Traits\UUID;
use App\Models\DetailDriver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory, UUID;

    protected $table = 'saldos';
    protected $primaryKey = 'id_saldo';
    protected $fillable = [
        'user_id',
        'saldo'
    ];

    public function detailDriver()
    {
        return $this->belongsTo(DetailDriver::class, 'user_id', 'user_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

}
