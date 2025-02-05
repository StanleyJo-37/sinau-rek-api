<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //
    protected $table = 'devices';

    protected $fillable = [
        'user_id',
    ];
    
    protected $guarded = [
        'secret_key',
        'mac_address',
        'publish_topic',
        'subscribe_topic',
    ];
}
