<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    //

    public function getAllTodos(Request $request)
    {
        try {
            $user = Auth::user();
            $user_id = $user->id;
            
            $request->validate([
                'team_id' => 'integer',
                'project_id' => 'integer',
            ]);

            $todos = DB::select("
                SELECT
                    t.id,
                    t.title todo_title,
                    t.description,
                    t.start_time,
                    t.deadline,
                    t.project_id,
                    p.title project_name,
                    t.name team_name
                FROM todos t
                LEFT JOIN todos_users tou ON tou.user_id = :user_id
                LEFT JOIN team_user tu ON tu.user_id = :user_id AND tu.team_id = :team_id
                LEFT JOIN teams t ON t.team_id = tu.team_id
                LEFT JOIN projects p ON p.id = t.project_id
                WHERE t.project_id = :project_id;
            ", [
                'user_id' => $user_id,
                'team_id' => $request->team_id,
                'project_id' => $request->project_id,
            ]);

            foreach($todos as $todo) {
                $todo->start_time = Carbon::parse($todo->start_time)->setTimezone('Asia/Indonesia')->toDayDateTimeString();
                $todo->deadline = Carbon::parse($todo->deadline)->setTimezone('Asia/Indonesia')->toDayDateTimeString();
            }

            return response()->json($todos);
        } catch (Exception $err) {
            return response()->json(
                ['message' => 'Error fetching todos. Please try again later.'],
                500
            );
        }
    }
}
