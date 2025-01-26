<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    //
    protected $table = 'projects_users';

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
