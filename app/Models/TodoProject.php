<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoProject extends Model
{
    //
    protected $table = 'todos_projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'todo_id',
        'project_id',
    ];
}
