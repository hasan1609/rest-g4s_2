<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory, UUID;

    protected $table = 'notification_logs';
    public $primaryKey = 'id_notification_log';
    protected $fillable = [
        'judul',
        'body',
        'data',
        'status',
        'sender_id',
        'recive_id'
    ];
}
