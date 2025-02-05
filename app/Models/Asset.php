<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    //
    protected $table = 'assets';

    protected $fillable = [
        'id',
        'path',
        'mime_type',
        'created_at',
        'updated_at',
    ];
}
