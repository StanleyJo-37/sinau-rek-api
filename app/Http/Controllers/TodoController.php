<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoProject;
use App\Models\TodoUser;
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
                    COALESCE(p.title, '') project_name,
                    COALESCE(te.name, '') team_name
                FROM todos t
                LEFT JOIN todos_users tou ON tou.user_id = :user_id
                LEFT JOIN team_user tu ON tu.user_id = :user_id AND tu.team_id = :team_id
                LEFT JOIN teams te ON te.id = tu.team_id
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
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error fetching todos. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function getTodo(int $todo_id)
    {
        try {
            $user = Auth::user();
            $user_id = $user->id;

            $todos = DB::select("
                SELECT
                    t.id,
                    t.title todo_title,
                    t.description,
                    t.start_time,
                    t.deadline,
                    p.id,
                    COALESCE(p.title, '') project_title
                FROM todos t
                LEFT JOIN todos_users tu ON tu.user_id = :user_id
                LEFT JOIN projects p ON p.id = t.project_id
                WHERE t.id = :todo_id;
            ", [
                'todo_id' => $todo_id,
                'user_id' => $user_id,
            ]);

            $todo = null;
            if (! empty($todos) && ! isset($todos[0])) {
                $todo = $todos[0];
            } else {
                return response()->json(
                    ['message' => 'Todo not found.'],
                    404,
                );
            }
            
            $todo->start_time = Carbon::parse($todo->start_time)->setTimezone('Asia/Indonesia')->toDayDateTimeString();
            $todo->deadline = Carbon::parse($todo->deadline)->setTimezone('Asia/Indonesia')->toDayDateTimeString();

            return response()->json($todo);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error fetching todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function createTodo(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $user_id = $user->id;

            $request->validate([
                'title' => 'string|required',
                'description' => 'string|required',
                'start_time' => 'date',
                'deadline' => 'date',
                'project_id' => 'integer',
            ]);

            $start_time = $request->start_time ? Carbon::parse($request->start_time)->setTimezone('UTC')->toDayDateTimeString() : null;
            $deadline = $request->deadline ? Carbon::parse($request->deadline)->setTimezone('UTC')->toDayDateTimeString() : null;

            $now = Carbon::now();

            $todo = Todo::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_time' => $start_time,
                'deadline' => $deadline,
                'project_id' => $request->project_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $todo_user = TodoUser::create([
                'todo_id' => $todo->id,
                'user_id' => $user_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            return response()->json($todo);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error creating todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function editTodo(Request $request, int $todo_id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $request->validate([
                'title' => 'string|required',
                'description' => 'string|required',
                'start_time' => 'date',
                'deadline' => 'date',
                'project_id' => 'integer',
            ]);

            $start_time = $request->start_time ? Carbon::parse($request->start_time)->setTimezone('UTC')->toDayDateTimeString() : null;
            $deadline = $request->deadline ? Carbon::parse($request->deadline)->setTimezone('UTC')->toDayDateTimeString() : null;

            $now = Carbon::now();

            $todo = Todo::findOrFail($todo_id);
            $todo->title = $request->title;
            $todo->description = $request->description;
            $todo->start_time = $start_time;
            $todo->deadline = $deadline;
            $todo->updated_at = $now;
            $todo->save();

            DB::commit();

            return response()->json($todo);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error creating todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function deleteTodo(int $todo_id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $todo = Todo::findOrFail($todo_id);
            $todo->delete();

            DB::commit();

            return response()->json($todo);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => 'Error creating todo. Please try again later.',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

}
