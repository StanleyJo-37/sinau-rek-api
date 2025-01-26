<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoUser extends Model
{
    //
    protected $table = 'todos_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'todo_id',
        'user_id',
    ];
}
