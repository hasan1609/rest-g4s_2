<?php

namespace App\Models;

use App\Traits\UUID;
use App\Models\DetailResto;
use App\Models\DetailCustomer;
use App\Models\DetailDriver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, UUID;

    protected $table = 'reviews';
    protected $primaryKey = 'id_review';
    protected $fillable = [
        'customer_id',
        'driver_id',
        'resto_id',
        'rating_driver',
        'rating_resto',
        'ulasan_driver',
        'ulasan_resto'
    ];

    public function userCust()
    {
        return $this->belongsTo(User::class,'customer_id', 'id_user');
    }

    public function resto()
    {
        return $this->belongsTo(DetailResto::class, 'resto_id', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(DetailCustomer::class, 'customer_id', 'user_id');
    }
    public function driver()
    {
        return $this->belongsTo(DetailDriver::class, 'driver_id', 'user_id');
    }
}
