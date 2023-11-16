<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ongkir extends Model
{
    use HasFactory;
    protected $table = 'ongkirs';
    public $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'ongkir'
    ];
}
